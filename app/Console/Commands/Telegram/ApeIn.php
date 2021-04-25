<?php

namespace App\Console\Commands\Telegram;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Log;

class ApeIn extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'apein';

    /**
     * @var string Command Description
     */
    protected $description = 'Command to start Bot features ';

    /**
     * @inheritdoc
     */
    public function handle($arguements)
    {
        Log::info('apein',$arguements);
        $this->replyWithMessage(['text' => 'Hello! Welcome to our bot, Here are our available commands:']);

       // return 0;
    }
}
