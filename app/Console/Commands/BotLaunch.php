<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Circle;

class BotLaunch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:launch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $circle = Circle::find(1);
        $circle->notify(new \App\Notifications\BotLaunch());
    }
}
