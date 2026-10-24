<?php

namespace App\Support;

use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Topic;
use App\Models\Vote;

final class Presenter
{
    /**
     * @param  array<string, int|float|bool|null>|null  $metrics
     * @return array<string, mixed>
     */
    public static function campaign(Campaign $campaign, ?array $metrics = null): array
    {
        return [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'question' => $campaign->question,
            'template_name' => $campaign->template_name,
            'template_lang' => $campaign->template_lang,
            'status' => $campaign->status->value,
            'audience' => $campaign->audience,
            'vote_options' => $campaign->vote_options ?? [],
            'article_title' => $campaign->article_title,
            'article_url' => $campaign->article_url,
            'article_note' => $campaign->article_note,
            'sent_at' => $campaign->sent_at?->toIso8601String(),
            'closes_at' => $campaign->closes_at?->toIso8601String(),
            'article_sent_at' => $campaign->article_sent_at?->toIso8601String(),
            'replies_count' => (int) ($campaign->replies_count ?? 0),
            'voice_count' => (int) ($campaign->voice_count ?? 0),
            'has_test_data' => (bool) ($campaign->has_test_data ?? false),
            'metrics' => $metrics,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function message(Message $message): array
    {
        $transcript = $message->transcript;
        $normalized = QuoteAnonymizer::clean($transcript?->normalized_text);
        $raw = QuoteAnonymizer::clean($transcript?->raw_text ?: $message->body);
        $primary = $normalized !== '' ? $normalized : $raw;

        return [
            'id' => $message->id,
            'type' => $message->type->value,
            'body' => $primary,
            'original' => $raw !== $primary ? $raw : null,
            'summary' => QuoteAnonymizer::clean($transcript?->summary_msa),
            'language' => $transcript?->language,
            'neighborhood' => $message->contact->neighborhood,
            'is_test_data' => $message->is_test_data,
            'audio_url' => $message->media_path !== null && $message->campaign_id !== null
                ? route('messages.audio', ['campaign' => $message->campaign_id, 'message' => $message->id])
                : null,
            'duration_sec' => $message->duration_sec,
            'received_at' => $message->received_at?->toIso8601String(),
            'edited' => $transcript?->edited_by !== null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function topic(Topic $topic, int $totalReplies): array
    {
        $neighborhoods = [];
        $languages = [];

        foreach ($topic->messages as $message) {
            $neighborhood = $message->contact->neighborhood;

            if (is_string($neighborhood) && $neighborhood !== '') {
                $neighborhoods[$neighborhood] = ($neighborhoods[$neighborhood] ?? 0) + 1;
            }

            $language = $message->transcript?->language;

            if (is_string($language) && $language !== '') {
                $languages[$language] = ($languages[$language] ?? 0) + 1;
            }
        }

        arsort($neighborhoods);
        arsort($languages);

        $quotes = [];
        $ordered = $topic->messages->sortByDesc(function (Message $message): int {
            $flag = $message->pivot->is_featured_quote ?? false;

            return $flag ? 1 : 0;
        })->values();

        foreach ($ordered->take(3) as $message) {
            $quotes[] = [
                'message_id' => $message->id,
                'text' => QuoteAnonymizer::clean($message->readableText()),
                'language' => $message->transcript?->language,
                'neighborhood' => $message->contact->neighborhood,
                'audio_url' => $message->media_path !== null
                    ? route('messages.audio', ['campaign' => $topic->campaign_id, 'message' => $message->id])
                    : null,
                'is_test_data' => $message->is_test_data,
            ];
        }

        $topNeighborhoods = [];

        foreach (array_slice($neighborhoods, 0, 3, true) as $name => $count) {
            $topNeighborhoods[] = ['name' => (string) $name, 'count' => $count];
        }

        return [
            'id' => $topic->id,
            'label_ar' => $topic->label_ar,
            'label_fr' => $topic->label_fr,
            'summary' => $topic->summary,
            'replies_count' => $topic->replies_count,
            'share' => $totalReplies > 0 ? (int) round(($topic->replies_count / $totalReplies) * 100) : 0,
            'is_locked' => $topic->is_locked,
            'neighborhoods' => $topNeighborhoods,
            'languages' => array_keys($languages),
            'quotes' => $quotes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contact(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'masked_phone' => $contact->maskedPhone(),
            'neighborhood' => $contact->neighborhood,
            'language_pref' => $contact->language_pref,
            'channel' => $contact->channel->value,
            'consent_at' => $contact->consent_at?->toIso8601String(),
            'consent_source' => $contact->consent_source,
            'opted_out_at' => $contact->opted_out_at?->toIso8601String(),
            'is_test_data' => $contact->is_test_data,
            'subscribed' => $contact->canReceive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function broadcast(Broadcast $broadcast): array
    {
        return [
            'id' => $broadcast->id,
            'kind' => $broadcast->kind->value,
            'template_name' => $broadcast->template_name,
            'total' => $broadcast->total,
            'sent' => $broadcast->sent,
            'delivered' => $broadcast->delivered,
            'read' => $broadcast->read,
            'failed' => $broadcast->failed,
            'cost_estimate' => (float) $broadcast->cost_estimate,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function voteTally(Campaign $campaign): array
    {
        $total = $campaign->votes()->count();
        $rows = [];

        foreach ($campaign->vote_options ?? [] as $option) {
            if (! is_array($option) || ! is_numeric($option['topic_id'] ?? null)) {
                continue;
            }

            $topicId = (int) $option['topic_id'];
            $count = Vote::query()->where('campaign_id', $campaign->id)->where('topic_id', $topicId)->count();
            $rows[] = [
                'topic_id' => $topicId,
                'label_ar' => is_string($option['label_ar'] ?? null) ? $option['label_ar'] : '',
                'label_fr' => is_string($option['label_fr'] ?? null) ? $option['label_fr'] : '',
                'count' => $count,
                'share' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        }

        usort($rows, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }
}
