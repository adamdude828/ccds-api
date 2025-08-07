<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoMode;
use App\Models\VideoStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'video_id' => $this->faker->unique()->numerify('VID###'),
            'subject_id' => Subject::factory(),
            'grade_id' => Grade::factory(),
            'title' => $this->faker->sentence(3),
            'day' => $this->faker->numberBetween(1, 365),
            'description' => $this->faker->paragraph(),
            'guid' => $this->faker->uuid(),
            'streaming_url' => '/videos/' . $this->faker->slug() . '.m3u8',
            'poster' => $this->faker->slug() . '.jpg',
            'video_status_id' => VideoStatus::factory(),
            'video_mode_id' => VideoMode::factory(),
            'uploader_user_id' => User::factory(),
            'meta' => [
                'input' => [
                    'guid' => $this->faker->uuid(),
                    'container' => 'inc' . $this->faker->uuid(),
                    'asset' => $this->faker->uuid() . '-IN',
                    'job' => $this->faker->uuid() . '-JOB',
                    'file' => $this->faker->uuid() . '.mp4'
                ],
                'streaming-locator' => $this->faker->uuid() . '-STREAMING',
                'output' => [
                    'container' => 'outc' . $this->faker->uuid(),
                    'asset' => $this->faker->uuid() . '-OUT',
                    'poster-container' => 'outcp' . $this->faker->uuid(),
                    'poster-asset' => $this->faker->uuid() . '-pout',
                    'poster-image' => 'POSTER-' . $this->faker->uuid() . '.png'
                ]
            ]
        ];
    }
} 