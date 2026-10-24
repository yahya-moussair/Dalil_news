<?php

namespace App\Models;

use App\Enums\BroadcastKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $campaign_id
 * @property BroadcastKind $kind
 * @property string|null $template_name
 * @property int $total
 * @property int $sent
 * @property int $delivered
 * @property int $read
 * @property int $failed
 * @property string $cost_estimate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'campaign_id',
    'kind',
    'template_name',
    'total',
    'sent',
    'delivered',
    'read',
    'failed',
    'cost_estimate',
])]
class Broadcast extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => BroadcastKind::class,
            'total' => 'integer',
            'sent' => 'integer',
            'delivered' => 'integer',
            'read' => 'integer',
            'failed' => 'integer',
            'cost_estimate' => 'decimal:4',
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
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
