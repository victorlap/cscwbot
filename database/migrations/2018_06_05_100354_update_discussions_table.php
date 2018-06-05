<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDiscussionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->enum('state', ['add_arguments', 'rate_arguments', 'voting', 'closed'])->default('add_arguments')->nullable(false);
            $table->string('author');
            $table->unsignedInteger('result');
            $table->foreign('result')->references('id')->on('viewpoints');
        });

        Schema::table('viewpoints', function (Blueprint $table) {
            $table->unsignedInteger('discussion_id');
            $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn(['state', 'author', 'result']);
        });

        Schema::table('viewpoints', function (Blueprint $table) {
            $table->dropColumn('discussion_id');
        });
    }
}
