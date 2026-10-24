<?php

namespace App\Services\Clustering;

use App\Services\OpenAi\OpenAiClient;

class OpenAiClusteringService implements ClusteringService
{
    public function __construct(
        private readonly OpenAiClient $client,
        private readonly FakeClusteringService $fallback,
    ) {}

    public function cluster(array $replies): array
    {
        if ($replies === []) {
            return [];
        }

        $model = (string) config('services.openai.chat_model', 'gpt-4o-mini');
        $packed = [];

        foreach ($replies as $reply) {
            $packed[] = [
                'id' => $reply['message_id'],
                'text' => mb_substr($reply['text'], 0, 400),
                'neighborhood' => $reply['neighborhood'],
                'language' => $reply['language'],
            ];
        }

        $response = $this->client->post('chat/completions', [
            'model' => $model,
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You group short audience replies for a Moroccan newsroom. Replies may be Darija, Arabizi, MSA, or French. Return JSON {"topics":[{"label_ar":"...","label_fr":"...","summary":"...","message_ids":[1],"featured_message_ids":[1]}]}. label_ar is 2-5 Arabic words. label_fr is the French name. summary is one Modern Standard Arabic sentence and must not invent facts. Every id must come from the input. featured_message_ids is a subset of message_ids, max 3, picking the clearest real replies. Merge the same problem across neighborhoods and languages. Keep water cuts, street lighting, buses, trash, potholes, and sewage separate. Use 3 to 8 topics. Unclear replies go to "مواضيع أخرى" / "Autres sujets". Do not write quote text.',
                ],
                ['role' => 'user', 'content' => json_encode(['replies' => $packed], JSON_UNESCAPED_UNICODE) ?: '[]'],
            ],
        ]);

        if ($response->failed()) {
            return $this->fallback->cluster($replies);
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            return $this->fallback->cluster($replies);
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['topics']) || ! is_array($decoded['topics'])) {
            return $this->fallback->cluster($replies);
        }

        $topics = [];

        foreach ($decoded['topics'] as $topic) {
            if (! is_array($topic)) {
                continue;
            }

            $labelAr = $topic['label_ar'] ?? null;
            $labelFr = $topic['label_fr'] ?? null;
            $summary = $topic['summary'] ?? '';
            $ids = $topic['message_ids'] ?? [];
            $featured = $topic['featured_message_ids'] ?? [];

            if (! is_string($labelAr) || ! is_string($labelFr) || ! is_array($ids)) {
                continue;
            }

            $messageIds = [];

            foreach ($ids as $id) {
                if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                    $messageIds[] = (int) $id;
                }
            }

            $featuredIds = [];

            if (is_array($featured)) {
                foreach ($featured as $id) {
                    if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                        $featuredIds[] = (int) $id;
                    }
                }
            }

            if ($messageIds === []) {
                continue;
            }

            $topics[] = [
                'label_ar' => $labelAr,
                'label_fr' => $labelFr,
                'summary' => is_string($summary) ? $summary : '',
                'message_ids' => $messageIds,
                'featured_message_ids' => array_slice($featuredIds, 0, 3),
            ];
        }

        return $topics === [] ? $this->fallback->cluster($replies) : $topics;
    }
}
