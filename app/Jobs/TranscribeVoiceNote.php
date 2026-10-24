<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Transcript;
use App\Services\Messaging\MessagingManager;
use App\Services\Transcription\TranscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class TranscribeVoiceNote implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public function __construct(public Message $message) {}

    public function handle(TranscriptionService $transcription, MessagingManager $messaging): void
    {
        $message = $this->message->fresh(['transcript', 'contact']);

        if (! $message instanceof Message || $message->media_id === null) {
            return;
        }

        if ($message->transcript instanceof Transcript && is_string($message->transcript->raw_text) && $message->transcript->raw_text !== '') {
            if ($message->transcript->normalized_text === null) {
                NormalizeReply::dispatch($message);
            }

            return;
        }

        $contact = $message->contact;
        $path = $messaging->forContact($contact)->downloadMedia($message->media_id);
        $absolute = Storage::disk('local')->path($path);
        $result = $transcription->transcribe($absolute);

        $message->forceFill(['media_path' => $path])->save();

        Transcript::query()->updateOrCreate(
            ['message_id' => $message->id],
            [
                'raw_text' => $result['text'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'confidence' => $result['confidence'],
            ],
        );

        NormalizeReply::dispatch($message);
    }
}
