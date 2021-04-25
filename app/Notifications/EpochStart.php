<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use NotificationChannels\Telegram\TelegramChannel;


class EpochStart extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $epoch;
    public function __construct($epoch)
    {
        $this->epoch = $epoch;
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
        $name = $notifiable->protocol->name .'/'. $notifiable->name;
        $start_date = $this->epoch->start_date->format('Y/m/d h:i T');
        $end_date = $this->epoch->end_date->format('Y/m/d h:i T');
        $usersCount = $notifiable->users()->count();
        return TelegramMessage::create()
            // Optional recipient user id.
            ->to($notifiable->telegram_id)
            // Markdown supported.
            ->content("A new $name epoch is active !\n$usersCount users will be participating and the duration of the epoch will be between:\n$start_date - $end_date")
            ->button('Start Allocating GIVES', 'https://coordinape.com/'.$name);
    }

//    /**
//     * Get the mail representation of the notification.
//     *
//     * @param  mixed  $notifiable
//     * @return \Illuminate\Notifications\Messages\MailMessage
//     */
//    public function toMail($notifiable)
//    {
//        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
//    }

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
