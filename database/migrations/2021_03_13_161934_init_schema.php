<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InitSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("videos", function(Blueprint $table) {
            $table->increments('id');
            $table->string("title");
            $table->integer('video_status_id')->nullable();
            $table->integer("grade_id")->nullable();
            $table->integer("subject_id")->nullable();
            $table->integer("day")->nullable();
            $table->json('meta')->nullable();
            $table->string("streaming_url")->nullable();
            $table->string("poster")->nullable();
            $table->timestamps();
        });

        Schema::create("video_statuses", function(Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create("grades", function(Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->timestamps();
        });

        Schema::create("subjects", function(Blueprint  $table) {
            $table->increments("id");
            $table->string("name");
            $table->timestamps();
        });


        $grades = [
            'Preschool 3',
            'Preschool 4',
            'Kindergarten',
            '1st Grade',
            '2nd Grade',
            '3rd Grade',
            '4th Grade',
            '5th Grade',
            '6th Grade',
            '7th Grade',
            '8th Grade'
        ];
        foreach($grades as $grade) {
            \App\Models\Grade::create([
                'name'=>$grade
            ]);
        }

        $subjects = [
            'Phonics',
            'Math',
            'Storytime',
            'Logic',
            'Grammar / Composition',
            'Grammar',
            'Math',
            'Literature',
            'Spelling',
            'Science',
            'Literature / Vocab',
            'Econ',
            'History'
        ];

        foreach($subjects as $subject) {
            \App\Models\Subject::create([
                'name'=>$subject
            ]);
        }
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('videos');
        Schema::drop('video_statuses');
        Schema::drop('subjects');
        Schema::drop('grades');
        //
    }
}
