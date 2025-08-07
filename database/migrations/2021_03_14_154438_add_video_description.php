<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function(Blueprint $table) {
            $table->text('description')->after("title")->nullable();
        });

        \App\Models\VideoStatus::create([
                'name'=>\App\Models\VideoStatus::VIDEO_STATUS['UPLOAD_IN_PROGRESS'],
                'display_name'=>'Upload In Progress'
            ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['UPLOAD_COMPLETE'],
            'display_name'=>'Upload Complete'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['QUEUED_TRANSCODE'],
            'display_name'=>'Waiting For Transcode'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['TRANSCODE_IN_PROGRESS'],
            'display_name'=>'Transcode In Progress'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['TRANSCODE_COMPLETE'],
            'display_name'=>'Transcode Complete'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['QUEUED_FOR_POSTER'],
            'display_name'=>'Waiting for Poster'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['POSTER_IN_PROGRESS'],
            'display_name'=>'Poster In Progress'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['VIDEO_READY'],
            'display_name'=>'Ready'
        ]);

        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['VIDEO_ERROR'],
            'display_name'=>'Error Processing'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function(Blueprint $table) {
            $table->dropColumn('description');
        });
        //
    }
}
