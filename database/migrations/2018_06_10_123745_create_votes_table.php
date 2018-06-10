<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('discussion_id');
            $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
            $table->string('author');
            $table->unsignedInteger('viewpoint_id');
            $table->foreign('viewpoint_id')->references('id')->on('viewpoints')->onDelete('cascade');
            $table->unique(['discussion_id', 'author']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('votes');
    }
}
