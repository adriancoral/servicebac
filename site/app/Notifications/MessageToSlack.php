<?php

namespace App\Notifications;

use App\Models\PdfWork;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class MessageToSlack extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;

    public $attachment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title = 'Title', $attachment = [])
    {
        $this->title = $title;
        $this->attachment = $attachment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->error()
            ->content($this->title)
            ->attachment(function (SlackAttachment $attachment) use ($notifiable) {
                $attachment->fields($this->attachment);
            });
    }
}
