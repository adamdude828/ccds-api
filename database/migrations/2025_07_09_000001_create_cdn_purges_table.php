<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdn_purges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('paths');
            $table->string('status')->default('pending'); // pending, in_progress, succeeded, failed
            $table->string('operation_url')->nullable();
            $table->string('request_id')->nullable();
            $table->string('provider')->default('frontdoor');
            $table->string('profile_name')->nullable();
            $table->string('endpoint_name')->nullable();
            $table->string('resource_group')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdn_purges');
    }
};