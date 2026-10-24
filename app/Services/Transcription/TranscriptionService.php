<?php

namespace App\Services\Transcription;

interface TranscriptionService
{
    /**
     * @return array{text: string, language: string|null, confidence: float|null, model: string, provider: string}
     */
    public function transcribe(string $absolutePath): array;
}
