<?php

namespace App\Models;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $campaign_id
 * @property int $contact_id
 * @property int|null $broadcast_id
 * @property string|null $wa_message_id
 * @property MessageDirection $direction
 * @property MessageType $type
 * @property string|null $body
 * @property string|null $media_id
 * @property string|null $media_path
 * @property int|null $duration_sec
 * @property string $status
 * @property bool $is_test_data
 * @property Carbon|null $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Contact $contact
 * @property-read Campaign|null $campaign
 * @property-read Transcript|null $transcript
 */
#[Fillable([
    'campaign_id',
    'contact_id',
    'broadcast_id',
    'wa_message_id',
    'direction',
    'type',
    'body',
    'media_id',
    'media_path',
    'duration_sec',
    'status',
    'is_test_data',
    'received_at',
])]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_test_data' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => MessageDirection::class,
            'type' => MessageType::class,
            'is_test_data' => 'boolean',
            'received_at' => 'datetime',
            'duration_sec' => 'integer',
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
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Broadcast, $this>
     */
    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    /**
     * @return HasOne<Transcript, $this>
     */
    public function transcript(): HasOne
    {
        return $this->hasOne(Transcript::class);
    }

    /**
     * @return BelongsToMany<Topic, $this>
     */
    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topic_reply')
            ->withPivot(['score', 'is_featured_quote'])
            ->withTimestamps();
    }

    public function readableText(): string
    {
        $transcript = $this->transcript;

        if ($transcript !== null && is_string($transcript->normalized_text) && $transcript->normalized_text !== '') {
            return $transcript->normalized_text;
        }

        if ($transcript !== null && is_string($transcript->raw_text) && $transcript->raw_text !== '') {
            return $transcript->raw_text;
        }

        return $this->body ?? '';
    }
}
