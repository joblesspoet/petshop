<?php

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->bigInteger('category_id')->unsigned()->index();
            $table->string('title');
            $table->double('price');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', Product::AVAILABLE_STATUS);
            $table->timestamps();
            $table->date('deleted_at')->nullable();

            $table->foreign('category_id')
                ->references('id')->on(Models::table('categories'))
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
        Schema::dropIfExists('products');
    }
};
