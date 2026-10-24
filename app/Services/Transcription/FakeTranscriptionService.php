<?php

namespace App\Services\Transcription;

class FakeTranscriptionService implements TranscriptionService
{
    public function transcribe(string $absolutePath): array
    {
        $sidecar = $absolutePath.'.txt';
        $text = 'السلام عليكم، حنا فسيدي مومن الما كيتقطع كل نهار من الصباح حتى للعشية.';

        if (is_file($sidecar)) {
            $contents = file_get_contents($sidecar);

            if (is_string($contents) && trim($contents) !== '') {
                $text = trim($contents);
            }
        }

        return [
            'text' => $text,
            'language' => null,
            'confidence' => 0.5,
            'model' => 'fake',
            'provider' => 'fake',
        ];
    }
}
