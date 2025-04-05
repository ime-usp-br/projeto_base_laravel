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

    /**
     * Cria uma nova instância da notificação.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Obtém os canais de entrega da notificação.
     *
     * @param mixed $notifiable A entidade notificável.
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtém a URL de verificação para o notificável.
     *
     * @param mixed $notifiable A entidade notificável.
     * @return string A URL assinada temporariamente.
     */
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

    /**
     * Obtém a representação por e-mail da notificação.
     *
     * @param mixed $notifiable A entidade notificável.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
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