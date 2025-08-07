<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDraftStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\VideoStatus::create([
            'name'=>\App\Models\VideoStatus::VIDEO_STATUS['VIDEO_DRAFT'],
            'display_name'=>'Video Draft'
        ]);
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\Models\VideoStatus::where("name", \App\Models\VideoStatus::VIDEO_STATUS['VIDEO_DRAFT'])->delete();
        //
    }
}
