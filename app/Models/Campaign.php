<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $newsroom_id
 * @property int|null $user_id
 * @property string $title
 * @property string $question
 * @property string|null $template_name
 * @property string $template_lang
 * @property CampaignStatus $status
 * @property array<string, mixed>|null $audience
 * @property array<int, mixed>|null $vote_options
 * @property string|null $article_title
 * @property string|null $article_url
 * @property string|null $article_note
 * @property Carbon|null $sent_at
 * @property Carbon|null $closes_at
 * @property Carbon|null $article_sent_at
 * @property Carbon|null $clustered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Newsroom|null $newsroom
 */
#[Fillable([
    'newsroom_id',
    'user_id',
    'title',
    'question',
    'template_name',
    'template_lang',
    'status',
    'audience',
    'vote_options',
    'article_title',
    'article_url',
    'article_note',
    'sent_at',
    'closes_at',
    'article_sent_at',
    'clustered_at',
])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'audience' => 'array',
            'vote_options' => 'array',
            'sent_at' => 'datetime',
            'closes_at' => 'datetime',
            'article_sent_at' => 'datetime',
            'clustered_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return HasMany<Topic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<Broadcast, $this>
     */
    public function broadcasts(): HasMany
    {
        return $this->hasMany(Broadcast::class);
    }

    public function isOpen(): bool
    {
        if (! in_array($this->status, [CampaignStatus::Sent, CampaignStatus::Voting], true)) {
            return false;
        }

        return $this->closes_at === null || $this->closes_at->isFuture();
    }
}
