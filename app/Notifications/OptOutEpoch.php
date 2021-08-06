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

class OptOutEpoch extends Notification implements ShouldQueue
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
        $channels = [];
        if(config('telegram.token'))
            $channels[] = TelegramChannel::class;
        if($notifiable->discord_webhook)
            $channels[] = DiscordWebhookChannel::class;

        return $channels;
    }

    private function getContent() {
//        $circle_name = $notifiable->protocol->name .'/'. $notifiable->name;
        $name = Utils::cleanStr($this->user->name);
        return "$name opted out of the current epoch!\nA total of $this->totalRefunded GIVE was refunded\n$this->refundStr";
    }

    public function toTelegram($notifiable=null)
    {

        return TelegramMessage::create()
            // Markdown supported.
            ->content($this->getContent())
            ->button('Reallocate your GIVES', 'https://app.coordinape.com/allocation');
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
