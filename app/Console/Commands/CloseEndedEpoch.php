<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\EpochRepository;
use App\Models\Circle;

class CloseEndedEpoch extends Command
{

    protected $repo ;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close:epochs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close all epochs that have passed their end datetime';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        if(config("cron.{$this->signature}") === false)
            return false;

        $circles = Circle::pluck('id');
        foreach($circles as $circle_id) {
            $this->repo->endEpoch($circle_id);
        }
    }
}
