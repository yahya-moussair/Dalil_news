<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $message_id
 * @property string|null $raw_text
 * @property string|null $normalized_text
 * @property string|null $summary_msa
 * @property string|null $language
 * @property string|null $provider
 * @property string|null $model
 * @property string|null $confidence
 * @property int|null $edited_by
 * @property Carbon|null $edited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'message_id',
    'raw_text',
    'normalized_text',
    'summary_msa',
    'language',
    'provider',
    'model',
    'confidence',
    'edited_by',
    'edited_at',
])]
class Transcript extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
            'confidence' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Message, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
