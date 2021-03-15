<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\EpochRepository;

class TriggerEpochEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epoch:end {circle}';

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
        $this->repo->endEpoch($this->argument('circle'));
    }
}
