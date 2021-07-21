<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Epoch;
use App\Repositories\EpochRepository;

class CheckEpochNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:enotifications';

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
    protected $repo;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $epoches = Epoch::with(['circle.protocol'])->isActiveDate()->where(function($q) {
             return $q->whereNull('notified_start')->orWhereNull('notified_before_end');
        })->get();

        foreach($epoches as $epoch) {
            $circle = $epoch->circle;
            if(($circle->telegram_id || $circle->discord_webhook) && $epoch->ended == 0)
                $this->repo->checkEpochNotifications($epoch);
        }
    }
}
