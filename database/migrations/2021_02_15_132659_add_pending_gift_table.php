<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPendingGiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_token_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_address');
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_address');
            $table->integer('tokens');
            $table->text('note')->nullable();
            $table->timestamp('dts_created')->useCurrent();
            $table->timestamps();
            $table->unsignedBigInteger('circle_id');

            $table->foreign('circle_id')->references('id')->on('circles');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_token_gifts');
    }
}
