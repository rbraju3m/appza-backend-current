<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Your Password')

            // Custom header/logo
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.')

            // Action with reset URL
            ->action('Reset Password', url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)))

            ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')

            ->line('If you did not request a password reset, no further action is required.')

            // Add a small copyright/footer
            ->salutation('— Appza core. ');
    }
}
