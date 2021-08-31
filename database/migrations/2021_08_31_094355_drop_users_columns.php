<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUsersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('remember_token','discord_username',
                            'telegram_username','avatar','chat_id',
                            'ann_power','super','admin_view','regift_percent','is_hidden');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_username')->nullable();
            $table->string('discord_username')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('ann_power')->default(0);
            $table->string('chat_id')->nullable();
            $table->boolean('super')->default(0);
            $table->boolean('admin_view')->default(0);
            $table->integer('regift_percent')->default(0);
            $table->boolean('is_hidden')->default(0);
        });
    }
}
