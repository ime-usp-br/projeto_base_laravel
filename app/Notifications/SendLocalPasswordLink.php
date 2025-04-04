<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class SendLocalPasswordLink extends Notification implements ShouldQueue
{
    use Queueable;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
    
    public function toMail(object $notifiable): MailMessage
    {
        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $notifiable->getEmailForVerification()] 
        );

       return (new MailMessage)
           ->subject('Configure sua Senha Local')
           ->greeting('Olá!')
           ->line('Você solicitou a configuração de uma senha local para acessar o sistema.')
           ->line('Clique no botão abaixo para definir sua senha. Este link é válido por 1 hora.')
           ->action('Definir Senha Local', $signedUrl)
           ->line('Se você não solicitou isso, nenhuma ação é necessária.')
           ->salutation('Atenciosamente,');
    }
    
    public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}
