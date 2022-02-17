<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    /** @var string */
    private string $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $name = $notifiable instanceof User
            ? $notifiable->first_name
            : trans('passwords.mail.generic_recipient');

        return (new MailMessage())
            ->subject(trans('passwords.mail.subject', ['name' => config('app.name')]))
            ->greeting(trans('passwords.mail.greeting', ['name' => $name]))
            ->line(trans('passwords.mail.intro', ['name' => config('app.name')]))
            ->line(trans('passwords.mail.token', ['token' => $this->token]))
            ->line(trans('passwords.mail.outro1'))
            ->line(trans('passwords.mail.outro2', ['name' => config('app.name')]))
            ->salutation(trans('passwords.mail.salutation', ['name' => config('app.name')]));
    }
}
