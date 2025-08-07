<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class AddUid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table("videos", function(Blueprint $table) {
            $table->string('uid')->after("id")->nullable();
        });

//        //ids in the list need to be set to the uidi
//        $ids = getenv("UID_URLS");
//        \Illuminate\Support\Facades\DB::statement("update videos set uid = id where id not in ($ids)");
//        $ids = explode(",", $ids);
//        foreach($ids as $id) {
//            $video = \App\Models\Video::find($id);
//            $uid = Uuid::uuid4();
//            $video->uid = $uid->toString();
//            $video->save();
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function(Blueprint $table) {
            $table->dropColumn('uid');
        });

        //
    }
}
