<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCirclePercentageVouches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->decimal('min_vouches_percent')->default(50);
            $table->boolean('calculate_vouching_percent')->default(0);
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
            $table->dropColumn('min_vouches_percent', 'calculate_vouching_percent');
        });
    }
}
