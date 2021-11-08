<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PostgresMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postgres:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run updates to make app compatible with postgres';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Schema::table('epoches', function (Blueprint $table) {
            $table->boolean('ended')->default(false)->change();
        });

        Schema::table('circles', function (Blueprint $table) {
            $table->boolean('vouching')->default(false)->change();
            $table->boolean('default_opt_in')->default(false)->change();
            $table->boolean('team_selection')->default(true)->change();
            $table->boolean('only_giver_vouch')->default(true)->change();
            $table->boolean('is_verified')->default(false)->change();
        });

        Schema::table('nominees', function (Blueprint $table) {
            $table->boolean('ended')->default(false)->change();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('admin_view')->default(false)->change();
            $table->boolean('ann_power')->default(false)->change();
        });

        Schema::table('protocols', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('non_receiver')->default(true)->change();
            $table->boolean('epoch_first_visit')->default(true)->change();
            $table->boolean('non_giver')->default(false)->change();
            $table->boolean('fixed_non_receiver')->default(false)->change();
        });
    }
}
