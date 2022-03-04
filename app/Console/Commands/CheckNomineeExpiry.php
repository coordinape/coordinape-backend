<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\NominationRepository;

class CheckNomineeExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:nominees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether nominees has expired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $repo ;
    public function __construct(NominationRepository $repo)
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

        return $this->repo->checkExpiry();
    }
}
