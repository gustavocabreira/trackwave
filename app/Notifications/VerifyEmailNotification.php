<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Actions\Auth\GenerateUserVerificationTokenAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Uri;

final class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $token = GenerateUserVerificationTokenAction::execute($notifiable);

        $url = Uri::of(config('app.frontend_url'))
            ->withPath('/verify-email')
            ->withQuery(['token' => $token])
            ->toStringable();

        return (new MailMessage)
            ->from('trackwave.dev@gmail.com', 'Trackwave')
            ->greeting('Welcome to the application!')
            ->line('Please, verify your email address by clicking on the link below:')
            ->action('Verify email', $url)
            ->line('Thank you for using our application!');
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
