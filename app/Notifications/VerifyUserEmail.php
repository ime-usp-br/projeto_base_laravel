<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class VerifyUserEmail extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {

    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(1),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        return (new MailMessage)
                    ->subject('Verifique seu Endereço de Email')
                    ->greeting('Olá!')
                    ->line('Clique no botão abaixo para verificar seu endereço de email.')
                    ->action('Verificar Email', $verificationUrl)
                    ->line('Se você não criou uma conta, nenhuma ação é necessária.')
                    ->line('Este link expirará em 60 minutos.')
                    ->salutation('Atenciosamente,');
    }
    
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
