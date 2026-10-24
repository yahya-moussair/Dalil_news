<?php

namespace App\Services\Clustering;

use App\Support\TopicLexicon;

class FakeClusteringService implements ClusteringService
{
    public function cluster(array $replies): array
    {
        /** @var array<string, list<int>> $ids */
        $ids = [];
        /** @var array<string, list<array{id: int, score: int}>> $scores */
        $scores = [];

        foreach (TopicLexicon::definitions() as $definition) {
            $ids[$definition['key']] = [];
            $scores[$definition['key']] = [];
        }

        $otherIds = [];
        $otherScored = [];

        foreach ($replies as $reply) {
            $key = TopicLexicon::match($reply['text']);

            if ($key !== null && array_key_exists($key, $ids)) {
                $score = $this->scoreFor($key, $reply['text']);
                $ids[$key][] = $reply['message_id'];
                $scores[$key][] = ['id' => $reply['message_id'], 'score' => $score];

                continue;
            }

            $otherIds[] = $reply['message_id'];
            $otherScored[] = ['id' => $reply['message_id'], 'score' => mb_strlen($reply['text'])];
        }

        $topics = [];

        foreach (TopicLexicon::definitions() as $definition) {
            $key = $definition['key'];
            $topic = $this->emit(
                $definition['label_ar'],
                $definition['label_fr'],
                $definition['summary'],
                $ids[$key] ?? [],
                $scores[$key] ?? [],
            );

            if ($topic !== null) {
                $topics[] = $topic;
            }
        }

        $other = $this->emit(
            'مواضيع أخرى',
            'Autres sujets',
            'ردود متفرقة لم تجتمع في موضوع واضح.',
            $otherIds,
            $otherScored,
        );

        if ($other !== null) {
            $topics[] = $other;
        }

        return $topics;
    }

    private function scoreFor(string $key, string $text): int
    {
        foreach (TopicLexicon::definitions() as $definition) {
            if ($definition['key'] === $key) {
                return TopicLexicon::score($definition['keywords'], $text);
            }
        }

        return 0;
    }

    /**
     * @param  list<int>  $messageIds
     * @param  list<array{id: int, score: int}>  $scored
     * @return array{label_ar: string, label_fr: string, summary: string, message_ids: list<int>, featured_message_ids: list<int>}|null
     */
    private function emit(string $labelAr, string $labelFr, string $summary, array $messageIds, array $scored): ?array
    {
        if ($messageIds === []) {
            return null;
        }

        usort($scored, fn (array $a, array $b): int => $b['score'] <=> $a['score']);
        $featured = [];

        foreach (array_slice($scored, 0, 3) as $row) {
            $featured[] = $row['id'];
        }

        return [
            'label_ar' => $labelAr,
            'label_fr' => $labelFr,
            'summary' => $summary,
            'message_ids' => $messageIds,
            'featured_message_ids' => $featured,
        ];
    }
}
