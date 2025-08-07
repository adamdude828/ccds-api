<?php

namespace Database\Factories;

use App\Models\BrightcoveImportLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BrightcoveImportLog>
 */
class BrightcoveImportLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data_log' => [
                'video_exists_skipping' => [],
                'json_found_missing_video' => [],
                'new' => [],
                'error' => []
            ]
        ];
    }
} 