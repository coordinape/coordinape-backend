<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEpochTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epoches', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->useCurrent();
            $table->integer('circle_id');
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
        Schema::dropIfExists('epoches');
    }
}
