<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateWebHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:webhook {url}';

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
        $url = $this->argument('url');
        $client = new \GuzzleHttp\Client();
        $apiRequest = $client->request('GET',
            "https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/setWebhook?url=$url");
    }
}
