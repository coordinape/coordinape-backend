<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SnoerenDevelopment\DiscordWebhook\DiscordMessage;
use SnoerenDevelopment\DiscordWebhook\DiscordWebhookChannel;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class BotLaunch extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];
        if(config('telegram.token'))
            $channels[] = TelegramChannel::class;

        return $channels;
    }

    public function toTelegram($notifiable=null)
    {
        return TelegramMessage::create()
            // Markdown supported.
            ->content("Now sending GIVES via Telegram Bot is now exclusively available for yearn/community circle /commands to see what functionalities are possible !\nWe also have a new discord channel https://discord.gg/tegaa7wr\nDo reach out to @reeserj if you are interested to contribute or helping out as an MOD/Admin\nSubscribe via the bot to get reminder and important updates directly!")
            ->button('Subscribe for Updates', 'https://telegram.me/CoordinapeBot');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
