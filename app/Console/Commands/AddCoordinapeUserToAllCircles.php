<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Models\Circle;
use Illuminate\Console\Command;

class AddCoordinapeUserToAllCircles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ape:populate_coordinape_user {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to add a Coordinape user to all circles with given address';

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
        $address = strtolower($this->argument('address'));

        $profile = Profile::firstOrCreate([
            'address' => $address,
        ]);

        # Iterate across all circles (TODO: can we do this in one transaction?)
        Circle::chunk(100, function ($circles) use ($address, $profile) {
            foreach ($circles as $circle) {
                User::firstOrCreate([
                    'address' => $address,
                    'name' => 'Coordinape',
                    'role' => config('enums.user_types.coordinape'),
                    'circle_id' => $circle->id
                ]);
            }
        });
    }
}
