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

    /**
     * O usuário para o qual a notificação está sendo enviada.
     *
     * @var \App\Models\User
     */
    protected User $user;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param \App\Models\User $user O usuário que receberá o link.
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Obtém os canais de entrega da notificação.
     *
     * @param object $notifiable A entidade notificável.
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtém a representação por e-mail da notificação.
     *
     * @param object $notifiable A entidade notificável.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
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

    /**
     * Obtém a representação em array da notificação.
     *
     * @param object $notifiable A entidade notificável.
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}