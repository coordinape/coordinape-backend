<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUxresearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uxresearch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('circle_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('protocol_id');
            $table->json('json')->nullable();
            $table->timestamps();
            $table->foreign('circle_id')->references('id')->on('circles');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('protocol_id')->references('id')->on('protocols');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uxresearch');
    }
}
