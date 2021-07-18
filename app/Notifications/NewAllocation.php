<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewAllocation extends Notification
{
    use Queueable;

    protected $user, $totalAllocated;
    public function __construct($user, $totalAllocated)
    {
        $this->user = $user;
        $this->totalAllocated = $totalAllocated;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return env('TELEGRAM_BOT_TOKEN') ? [TelegramChannel::class] : [];
    }

    public function toTelegram($notifiable=null)
    {
        $circle_name = $notifiable->protocol->name .'/'. $notifiable->name;
        $name = $this->user->telegram_username ?: $this->user->name;
        return TelegramMessage::create()
            // Markdown supported.
            ->content("$name has updated his allocation this epoch !\nA total of $this->totalAllocated GIVE is allocated\n")
            ->button("Check if you received GIVEs", 'https://app.coordinape.com/allocation');
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
