<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("video_modes", function(Blueprint $table) {
             $table->increments('id');
             $table->string("name");
             $table->timestamps();
        });

        // Insert video modes without specifying IDs for SQL Server compatibility
        $modes = [
            ['name' => \App\Models\VideoMode::READYMODE],
            ['name' => \App\Models\VideoMode::DRAFTMODE]
        ];
        
        foreach ($modes as $mode) {
            \App\Models\VideoMode::create($mode);
        }

        Schema::table('videos', function(Blueprint $table) {
            $table->integer("video_mode_id")->nullable()->after("video_status_id");
        });

        // Get the ID of the ready mode for update
        $readyModeId = \App\Models\VideoMode::where('name', \App\Models\VideoMode::READYMODE)->first()->id;
        \Illuminate\Support\Facades\DB::statement("update videos set video_mode_id = $readyModeId");


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("video_modes");

        Schema::table('videos', function(Blueprint $table) {
            $table->dropColumn('video_mode_id');
        });

    }
}
