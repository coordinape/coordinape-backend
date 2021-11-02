<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Models\Circle;
use Illuminate\Console\Command;
use App\Repositories\CircleRepository;

class AddCoordinapeUserToAllCircles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ape:populate_coordinape_user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to add a Coordinape user to all circles with address defined in ENV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CircleRepository $repo)
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
        $address = env('COORDINAPE_USER_ADDRESS');

        $profile = Profile::firstOrCreate([
            'address' => $address,
        ]);


        # Iterate across all circles (TODO: can we do this in one transaction?)
        Circle::chunk(100, function ($circles) {
            foreach ($circles as $circle) {
                $this->repo->addCoordinapeUserToCircle($circle->id);
            }
        });
    }
}
