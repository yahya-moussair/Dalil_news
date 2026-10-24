<?php

namespace Database\Factories;

use App\Models\Newsroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsroom>
 */
class NewsroomFactory extends Factory
{
    protected $model = Newsroom::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Casa Hay',
            'city' => 'Casablanca',
            'plan' => 'pilot',
            'settings' => ['display_number' => '212520000111'],
        ];
    }
}
