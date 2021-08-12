<?php

namespace App\Console\Commands;

use App\Models\Epoch;
use Illuminate\Console\Command;
use App\Repositories\EpochRepository;
use App\Models\Circle;

class TriggerDailyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:update';

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
        $epoches = Epoch::with(['circle.protocol','circle.pending_gifts','circle.users'])->isActiveDate()->where(function($q) {
            return $q->where('ended',0);
        })->get();

        foreach($epoches as $epoch) {
            if(($epoch->circle->telegram_id || $epoch->circle->discord_webhook) && $epoch->ended == 0)
                $this->repo->dailyUpdate($epoch);
        }
    }
}
