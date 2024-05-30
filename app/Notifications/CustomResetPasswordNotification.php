<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
       
        $this->expireIn = Carbon::now()->utc()->addMinutes(2);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Your Custom Reset Password Subject')
                    ->greeting('Hello!')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', url(config('app.url').route('reset-password-view', [$this->token,$this->expireIn], false)))
                    ->line('This password reset link will expire in 1 minutes.')
                    ->line('If you did not request a password reset, no further action is required.')
                    ->salutation('Regards, Your Application');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
