<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\Newsroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'newsroom_id' => Newsroom::factory(),
            'user_id' => User::factory(),
            'title' => 'سؤال الحي',
            'question' => 'ما هو أكبر مشكل كتعيشوه فالحي ديالكم؟',
            'template_name' => 'daily_question',
            'template_lang' => 'ar',
            'status' => CampaignStatus::Draft,
        ];
    }
}
