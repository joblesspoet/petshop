<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\Database\Models;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->json('metadata');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on(Models::table('users'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
