<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->unique();
            $table->integer('give_token_received')->default(0);
            $table->integer('give_token_remaining')->default(100);
            $table->integer('role')->default(0);
            $table->boolean('non_receiver')->default(1);
            $table->unsignedBigInteger('circle_id');
            $table->string('avatar')->nullable();
            $table->foreign('circle_id')->references('id')->on('circles');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
