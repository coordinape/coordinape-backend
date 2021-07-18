<?php

namespace App\Notifications;

use App\Helper\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class SendSocialMessage extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $message;
    protected $sanitize;
    public function __construct($message, $sanitize = true)
    {
        $this->message = $message;
        $this->sanitize = $sanitize;
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
        $message = $this->sanitize? Utils::cleanStr($this->message): $this->message;
        return TelegramMessage::create()
            // Markdown supported.
            ->content($message);
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
