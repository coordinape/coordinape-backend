<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use App\Helper\Utils;
use SnoerenDevelopment\DiscordWebhook\DiscordMessage;
use SnoerenDevelopment\DiscordWebhook\DiscordWebhookChannel;

class EpochAlmostEnd extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $unallocated_users, $circle_name;
    public function __construct($circle_name,$unallocated_users = [])
    {
        $this->circle_name = $circle_name;
        $this->unallocated_users = $unallocated_users;
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
        if($notifiable->discord_webhook)
            $channels[] = DiscordWebhookChannel::class;

        return $channels;
    }

    private function getContent()
    {
        $name = '_'.$this->circle_name.'_';
        $unalloc_users = $this->unallocated_users;
        $unalloc_str = '';
        foreach($unalloc_users as $user) {
            if($unalloc_str)
                $unalloc_str .= ', ';
            $user_name = Utils::cleanStr($user->name) ;
            $unalloc_str .= $user_name;
        }

        if($unalloc_str) {
            $unalloc_str = "Users that has yet to fully allocated their GIVE tokens:\n" . $unalloc_str;
        }
        else {
            $unalloc_str = "All users has fully allocated all their GIVE tokens !";
        }

        return "$name epoch is almost ending in less than 24HRS !\n$unalloc_str";
    }


    public function toTelegram($notifiable=null)
    {
        return TelegramMessage::create()
            // Markdown supported.
            ->content($this->getContent())
            ->button('Start Allocating GIVES', 'https://app.coordinape.com/allocation');
    }

    public function toDiscord($notifiable=null)
    {
        return DiscordMessage::create()
            ->content($this->getContent());

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
