<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_comments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('menu_id');
            $table->bigInteger('user_id');
            $table->float('rating', 2, 1);
            $table->text('comment');

            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('menus');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_comments');
    }
}
