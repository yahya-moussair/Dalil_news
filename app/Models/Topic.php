<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $label_ar
 * @property string $label_fr
 * @property string|null $summary
 * @property int $replies_count
 * @property array<string, mixed>|null $centroid
 * @property bool $is_locked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'campaign_id',
    'label_ar',
    'label_fr',
    'summary',
    'replies_count',
    'centroid',
    'is_locked',
])]
class Topic extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'replies_count' => 'integer',
            'centroid' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsToMany<Message, $this>
     */
    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'topic_reply')
            ->withPivot(['score', 'is_featured_quote'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
