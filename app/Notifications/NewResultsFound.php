<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;   
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewResultsFound extends Notification
{
    use Queueable;


    private $searchUrl;
    private $searchString;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $searchString, string $searchUrl)
    {
        $this->searchString = $searchString;
        $this->searchUrl = $searchUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("New results for \"$this->searchString\"")
            ->action($this->searchUrl, $this->searchUrl);
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
