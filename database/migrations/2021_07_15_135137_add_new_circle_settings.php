<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewCircleSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->boolean('vouching')->default(1);
            $table->integer('min_vouches')->default(2);
            $table->integer('nomination_days_limit')->default(14);
            $table->text('vouching_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->dropColumn('vouching','min_vouches','nomination_days_limit','vouching_text');
        });
    }
}
