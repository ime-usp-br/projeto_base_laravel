<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\SendResetPasswordLink;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config; // Import Config facade
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendResetPasswordLinkTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    public function test_send_reset_password_link_mail_content(): void
    {

        $user = User::factory()->make(['email' => 'reset@example.com']);
        $token = 'test_reset_token';

        $notification = new SendResetPasswordLink($token);

        $mailable = $notification->toMail($user);

        $expectedUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]));
        $expiryMinutes = config('auth.passwords.users.expire');

        $this->assertEquals('Redefinição de Senha', $mailable->subject);
        $this->assertStringContainsString('Olá!', $mailable->render());
        $this->assertStringContainsString('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.', $mailable->render());
        $this->assertStringContainsString($expectedUrl, $mailable->render());
        $this->assertEquals('Redefinir Senha', $mailable->actionText);
        $this->assertEquals($expectedUrl, $mailable->actionUrl);
        $this->assertStringContainsString('Este link de redefinição de senha expirará em ' . $expiryMinutes . ' minutos.', $mailable->render());
        $this->assertStringContainsString('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.', $mailable->render());
        $this->assertStringContainsString('Atenciosamente,', $mailable->render());
    }
}