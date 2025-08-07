<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('azure_id')->nullable();
            $table->json('azure_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('refresh_token')->nullable();
            
            // Add unique index that ignores NULL values for SQL Server
            $table->index('azure_id');
        });
        
        // Create a filtered unique index that excludes NULL values
        DB::statement('CREATE UNIQUE INDEX users_azure_id_unique ON users(azure_id) WHERE azure_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the filtered unique index first
        DB::statement('DROP INDEX IF EXISTS users_azure_id_unique ON users');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['azure_id']);
            $table->dropColumn(['azure_id', 'azure_token', 'token_expires_at', 'refresh_token']);
        });
    }
};
