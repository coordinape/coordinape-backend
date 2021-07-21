<?php

namespace App\Notifications;

use App\Helper\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use SnoerenDevelopment\DiscordWebhook\DiscordMessage;
use SnoerenDevelopment\DiscordWebhook\DiscordWebhookChannel;

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
        $channels = [];
        if(config('telegram.token'))
            $channels[] = TelegramChannel::class;
        if($notifiable->discord_webhook)
            $channels[] = DiscordWebhookChannel::class;

        return $channels;
    }

    private function getContent() {
        return  $this->sanitize? Utils::cleanStr($this->message): $this->message;
    }

    public function toTelegram($notifiable=null)
    {
        return TelegramMessage::create()
            // Markdown supported.
            ->content($this->getContent());
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
