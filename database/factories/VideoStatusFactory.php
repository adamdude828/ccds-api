<?php

namespace Database\Factories;

use App\Models\VideoStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VideoStatus>
 */
class VideoStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusName = $this->faker->randomElement(array_values(VideoStatus::VIDEO_STATUS));
        $displayName = array_search($statusName, VideoStatus::VIDEO_STATUS);
        
        return [
            'name' => $statusName,
            'display_name' => $displayName
        ];
    }
} 