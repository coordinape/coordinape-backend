<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Models\Protocol;
use App\Models\Circle;
use Illuminate\Console\Command;

class QuickStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ape:quickstart {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to quickly help setup a functioning circle with the user\'s address input';

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
        $protocol = Protocol::firstOrCreate([
            'name' => 'testprotocol'
        ]);
        $circle = Circle::firstOrCreate([
           'name' => 'testcircle', 'protocol_id' => $protocol->id
        ]);
        User::firstOrCreate([
            'address' => $address, 'role' => 1, 'name' => 'Admin User', 'circle_id' => $circle->id
        ]);
        Profile::firstOrCreate([
            'address' => $address
        ]);
    }
}
