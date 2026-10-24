<?php

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Enums\MessageType;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Transcript;
use App\Services\Normalization\NormalizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class NormalizeReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Message $message) {}

    public function handle(NormalizationService $normalization): void
    {
        $message = $this->message->fresh(['transcript', 'campaign']);

        if (! $message instanceof Message) {
            return;
        }

        $transcript = $message->transcript;
        $source = $transcript?->raw_text ?: $message->body;

        if (! is_string($source) || trim($source) === '') {
            return;
        }

        if (! $transcript instanceof Transcript) {
            $transcript = new Transcript(['message_id' => $message->id, 'raw_text' => $source]);
        } elseif ($transcript->raw_text === null || $transcript->raw_text === '') {
            $transcript->raw_text = $source;
        }

        if ($transcript->edited_by === null) {
            $result = $normalization->normalize($source);
            $transcript->normalized_text = $result['normalized_text'];
            $transcript->summary_msa = $result['summary_msa'];
            $transcript->language = $result['language'];
        }

        $transcript->save();

        $campaign = $message->campaign;

        if (! $campaign instanceof Campaign) {
            return;
        }

        if (! in_array($message->type, [MessageType::Text, MessageType::Audio], true)) {
            return;
        }

        if (! in_array($campaign->status, [CampaignStatus::Sent, CampaignStatus::Voting], true)) {
            return;
        }

        if ((string) config('dalil.drivers.clustering') === 'openai'
            && ! Cache::add('dalil-cluster-'.$campaign->id, 1, 45)) {
            return;
        }

        ClusterReplies::dispatch($campaign);
    }
}
