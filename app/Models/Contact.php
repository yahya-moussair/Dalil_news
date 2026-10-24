<?php

namespace App\Models;

use App\Enums\ContactChannel;
use App\Support\Phone;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $newsroom_id
 * @property string $wa_id
 * @property string $wa_id_hash
 * @property string|null $display_name
 * @property string|null $neighborhood
 * @property string|null $language_pref
 * @property Carbon|null $consent_at
 * @property string|null $consent_source
 * @property string|null $consent_text_version
 * @property Carbon|null $opted_out_at
 * @property Carbon|null $last_inbound_at
 * @property ContactChannel $channel
 * @property bool $is_test_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Newsroom $newsroom
 */
#[Fillable([
    'newsroom_id',
    'wa_id',
    'wa_id_hash',
    'display_name',
    'neighborhood',
    'language_pref',
    'consent_at',
    'consent_source',
    'consent_text_version',
    'opted_out_at',
    'last_inbound_at',
    'channel',
    'is_test_data',
])]
#[Hidden(['wa_id', 'wa_id_hash', 'display_name'])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_test_data' => false,
    ];

    protected static function booted(): void
    {
        static::saving(function (Contact $contact): void {
            $plain = $contact->wa_id;

            if ($plain === '') {
                return;
            }

            $normalized = Phone::normalize($plain);

            if ($normalized !== $plain) {
                $contact->wa_id = $normalized;
            }

            $contact->wa_id_hash = Phone::hash($normalized);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wa_id' => 'encrypted',
            'consent_at' => 'datetime',
            'opted_out_at' => 'datetime',
            'last_inbound_at' => 'datetime',
            'channel' => ContactChannel::class,
            'is_test_data' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Newsroom, $this>
     */
    public function newsroom(): BelongsTo
    {
        return $this->belongsTo(Newsroom::class);
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function maskedPhone(): string
    {
        return Phone::mask($this->wa_id);
    }

    public function canReceive(): bool
    {
        return $this->consent_at !== null && $this->opted_out_at === null;
    }

    public function withinServiceWindow(): bool
    {
        return $this->last_inbound_at !== null
            && $this->last_inbound_at->greaterThan(now()->subHours(24));
    }

    public static function fromSender(Newsroom $newsroom, string $senderId, ContactChannel $channel, ?string $name = null): self
    {
        $normalized = Phone::normalize($senderId);
        $hash = Phone::hash($normalized);

        $existing = static::query()
            ->where('newsroom_id', $newsroom->id)
            ->where('wa_id_hash', $hash)
            ->first();

        if ($existing instanceof self) {
            if ($name !== null && $name !== '' && ($existing->display_name === null || $existing->display_name === '')) {
                $existing->display_name = $name;
                $existing->save();
            }

            return $existing;
        }

        $contact = new self([
            'newsroom_id' => $newsroom->id,
            'channel' => $channel,
            'display_name' => $name,
        ]);
        $contact->wa_id = $normalized;
        $contact->wa_id_hash = $hash;

        try {
            $contact->save();
        } catch (QueryException $exception) {
            $raced = static::query()
                ->where('newsroom_id', $newsroom->id)
                ->where('wa_id_hash', $hash)
                ->first();

            if ($raced instanceof self) {
                return $raced;
            }

            throw $exception;
        }

        return $contact;
    }
}
