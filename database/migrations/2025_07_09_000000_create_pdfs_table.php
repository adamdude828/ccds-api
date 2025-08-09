<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdfs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('path')->unique(); // e.g., documents/<uuid>.pdf
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('content_type')->default('application/pdf');
            $table->string('etag')->nullable();
            $table->string('hash_sha256')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdfs');
    }
};