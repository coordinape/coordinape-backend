<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;

class CreateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:token {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a login token for an address that can be also used as an API key';

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
        $profile = Profile::byAddress($address)->first();
        if (!$profile) {
            $this->error("$address doesn't exist in the system!");
            return false;
        }
        if ($profile->tokens()->count()) {
            if (!$this->confirm('User already has an existing token do you want to reset it?', true)) {
                $this->error("Token generation has been cancelled!");
                return false;
            }
            $profile->tokens()->delete();
        }
        $token = $profile->createToken('circle-access-token', ['read'])->plainTextToken;
        $this->line("Token is successfully generated for $address!");
        $this->info("\n$token\n");
    }
}
