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
    protected $epoch, $circle_name, $circle;
    public function __construct($epoch, $circle_name, $circle)
    {
        $this->epoch = $epoch;
        $this->circle_name = $circle_name;
        $this->circle = $circle;

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
        $name = '_'.$this->circle_name.'_';
        $start_date = $this->epoch->start_date->format('Y/m/d H:i T');
        $end_date = $this->epoch->end_date->format('Y/m/d H:i T');
        $usersCount = $this->circle->users()->where('is_hidden',0)->count();
        return TelegramMessage::create()
            // Markdown supported.
            ->content("A new $name epoch is active !\n$usersCount users will be participating and the duration of the epoch will be between:\n$start_date - $end_date")
            ->button('Start Allocating GIVES', 'https://app.coordinape.com/allocation');
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
