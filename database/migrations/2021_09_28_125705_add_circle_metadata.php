<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircleMetadata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circle_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('circle_id');
            $table->json('json')->nullable();
            $table->timestamps();
            $table->foreign('circle_id')->references('id')->on('circles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circle_metadata');
    }
}
