<?php

namespace App\Models;

use Database\Factories\NewsroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $city
 * @property string|null $wa_phone_number_id
 * @property string $plan
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'city', 'wa_phone_number_id', 'plan', 'settings'])]
class Newsroom extends Model
{
    /** @use HasFactory<NewsroomFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public static function resolveForPhoneNumberId(?string $phoneNumberId): ?self
    {
        if (is_string($phoneNumberId) && $phoneNumberId !== '') {
            $match = static::query()->where('wa_phone_number_id', $phoneNumberId)->first();

            if ($match instanceof self) {
                return $match;
            }
        }

        $configured = config('services.whatsapp.phone_number_id');

        if (is_string($configured) && $configured !== '') {
            $match = static::query()->where('wa_phone_number_id', $configured)->first();

            if ($match instanceof self) {
                return $match;
            }
        }

        $fallback = static::query()->orderBy('id')->first();

        return $fallback instanceof self ? $fallback : null;
    }

    public function displayNumber(): string
    {
        $settings = $this->settings ?? [];
        $fromSettings = $settings['display_number'] ?? null;

        if (is_string($fromSettings) && $fromSettings !== '') {
            return $fromSettings;
        }

        $configured = config('services.whatsapp.display_number');

        return is_string($configured) ? $configured : '';
    }
}
