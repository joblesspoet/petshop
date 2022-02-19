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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->bigInteger('order_status_id')->unsigned()->index();
            $table->bigInteger('payment_id')->unsigned()->index();
            $table->string('uuid');
            $table->json('products');
            $table->json('address');
            $table->float('delivery_fee')->nullable();
            $table->float('amount');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on(Models::table('users'))
                ->onDelete('cascade');
            $table->foreign('order_status_id')
                ->references('id')->on(Models::table('order_status'))
                ->onDelete('cascade');
            $table->foreign('payment_id')
                ->references('id')->on(Models::table('payments'))
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
        Schema::dropIfExists('orders');
    }
};
