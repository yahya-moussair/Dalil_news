<?php

namespace App\Services\Normalization;

interface NormalizationService
{
    /**
     * @return array{language: string, normalized_text: string, summary_msa: string}
     */
    public function normalize(string $text): array;
}
