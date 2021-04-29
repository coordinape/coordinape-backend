<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class OptOutEpoch extends Notification
{
    use Queueable;

    protected $user, $totalRefunded, $refundStr;
    public function __construct($user, $totalRefunded, $refundStr)
    {
        $this->user = $user;
        $this->totalRefunded = $totalRefunded;
        $this->refundStr = $refundStr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable=null)
    {
        $circle_name = $notifiable->protocol->name .'/'. $notifiable->name;
        $name = $this->user->telegram_username ?: $this->user->name;
        return TelegramMessage::create()
            // Markdown supported.
            ->content("$name has just opt out of the current epoch !\nA total of $this->totalRefunded GIVE is refunded\n$this->refundStr")
            ->button('Reallocate your GIVES', 'https://coordinape.com/'.$circle_name);
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
