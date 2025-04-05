<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class SendResetPasswordLink extends ResetPasswordNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Obtém a representação por e-mail da notificação.
     *
     * @param mixed $notifiable A entidade notificável.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        $expiryMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->greeting('Olá!')
            ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir Senha', $url)
            ->line('Este link de redefinição de senha expirará em ' . $expiryMinutes . ' minutos.')
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.')
            ->salutation("Atenciosamente,");
    }
}