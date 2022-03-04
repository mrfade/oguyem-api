<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuCommentVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_comment_votes', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('menu_comment_id');
            $table->bigInteger('user_id');
            $table->tinyInteger('vote'); // 1 = upvote, -1 = downvote

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
        Schema::dropIfExists('menu_comment_votes');
    }
}
