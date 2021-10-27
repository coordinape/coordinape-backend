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
        $address = env('COORDINAPE_USER_ADDRESS');

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
                    'circle_id' => $circle->id,
                    'non_receiver' => 0,
                    'fixed_non_receiver' => 0,
                    'starting_tokens' => 0,
                    'non_giver' => 1,
                    'give_token_remaining' => 0,
                    'bio' => "Coordinape is that the platform you’re using right now! We currently offer our service for free and invite people to allocate to us from within your circles. All funds received go towards funding the team and our operations."
                ]);
            }
        });
    }
}
