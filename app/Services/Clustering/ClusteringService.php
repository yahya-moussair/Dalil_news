<?php

namespace App\Services\Clustering;

interface ClusteringService
{
    /**
     * @param  list<array{message_id: int, text: string, language: string|null, neighborhood: string|null}>  $replies
     * @return list<array{label_ar: string, label_fr: string, summary: string, message_ids: list<int>, featured_message_ids: list<int>}>
     */
    public function cluster(array $replies): array;
}
