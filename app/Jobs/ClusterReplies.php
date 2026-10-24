<?php

namespace App\Jobs;

use App\Events\TopicUpdated;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Topic;
use App\Services\Clustering\ClusteringService;
use App\Support\QuoteAnonymizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ClusterReplies implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public Campaign $campaign, public bool $force = false) {}

    public function handle(ClusteringService $clustering): void
    {
        $campaign = $this->campaign->fresh();

        if (! $campaign instanceof Campaign) {
            return;
        }

        $lockedMessageIds = Message::query()
            ->where('campaign_id', $campaign->id)
            ->whereHas('topics', function ($query): void {
                $query->where('is_locked', true);
            })
            ->pluck('id')
            ->all();

        $messages = $campaign->messages()
            ->where('direction', 'in')
            ->whereIn('type', ['text', 'audio'])
            ->whereNotIn('id', $lockedMessageIds === [] ? [0] : $lockedMessageIds)
            ->with(['transcript', 'contact'])
            ->get();

        $replies = [];

        foreach ($messages as $message) {
            $normalized = $message->transcript?->normalized_text;
            $raw = $message->transcript?->raw_text;
            $combined = trim(implode("\n", array_filter([
                is_string($normalized) ? $normalized : null,
                is_string($raw) ? $raw : null,
                $message->body,
            ])));
            $combined = QuoteAnonymizer::clean($combined);

            if ($combined === '') {
                continue;
            }

            $replies[] = [
                'message_id' => $message->id,
                'text' => mb_substr($combined, 0, 500),
                'language' => $message->transcript?->language,
                'neighborhood' => $message->contact->neighborhood,
            ];
        }

        if ($replies === []) {
            $campaign->forceFill(['clustered_at' => now()])->save();

            return;
        }

        $result = $clustering->cluster($replies);
        $valid = [];

        foreach ($replies as $reply) {
            $valid[$reply['message_id']] = true;
        }

        $used = [];
        $cleanTopics = [];

        foreach ($result as $topic) {
            $ids = [];

            foreach ($topic['message_ids'] as $id) {
                if (isset($valid[$id]) && ! isset($used[$id])) {
                    $ids[] = $id;
                    $used[$id] = true;
                }
            }

            if ($ids === []) {
                continue;
            }

            $featured = [];

            foreach ($topic['featured_message_ids'] as $id) {
                if (in_array($id, $ids, true)) {
                    $featured[] = $id;
                }
            }

            if ($featured === []) {
                $featured = array_slice($ids, 0, min(2, count($ids)));
            }

            $cleanTopics[] = [
                'label_ar' => $topic['label_ar'],
                'label_fr' => $topic['label_fr'],
                'summary' => $topic['summary'],
                'message_ids' => $ids,
                'featured_message_ids' => array_slice($featured, 0, 3),
            ];
        }

        $leftovers = [];

        foreach (array_keys($valid) as $id) {
            if (! isset($used[$id])) {
                $leftovers[] = $id;
            }
        }

        if ($leftovers !== []) {
            $cleanTopics[] = [
                'label_ar' => 'مواضيع أخرى',
                'label_fr' => 'Autres sujets',
                'summary' => 'ردود متفرقة لم تجتمع في موضوع واضح.',
                'message_ids' => $leftovers,
                'featured_message_ids' => array_slice($leftovers, 0, 2),
            ];
        }

        DB::transaction(function () use ($campaign, $cleanTopics): void {
            $campaign->topics()->where('is_locked', false)->delete();

            foreach ($cleanTopics as $topicData) {
                $topic = Topic::query()->create([
                    'campaign_id' => $campaign->id,
                    'label_ar' => $topicData['label_ar'],
                    'label_fr' => $topicData['label_fr'],
                    'summary' => $topicData['summary'],
                    'replies_count' => count($topicData['message_ids']),
                    'is_locked' => false,
                ]);

                $featured = array_fill_keys($topicData['featured_message_ids'], true);
                $attach = [];

                foreach ($topicData['message_ids'] as $id) {
                    $attach[$id] = [
                        'score' => isset($featured[$id]) ? 1 : 0.5,
                        'is_featured_quote' => isset($featured[$id]),
                    ];
                }

                $topic->messages()->attach($attach);
            }

            $campaign->forceFill(['clustered_at' => now()])->save();
        });

        event(new TopicUpdated($campaign));
    }
}
