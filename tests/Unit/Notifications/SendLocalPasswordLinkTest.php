<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\SendLocalPasswordLink;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendLocalPasswordLinkTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Testa o conteúdo do e-mail de link de senha local.
     *
     * @return void
     */
    public function test_send_local_password_link_mail_content(): void
    {
        config(['app.url' => 'http://localhost']);

        $user = User::factory()->make([
            'id' => 5,
            'email' => 'localuser@usp.br',
        ]);

        $notification = new SendLocalPasswordLink($user);

        $expectedSignedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->getEmailForVerification()]
        );

        $urlParts = parse_url($expectedSignedUrl);
        $pathAndQuery = $urlParts['path'] . '?' . explode('&signature=', $urlParts['query'])[0];

        $mailable = $notification->toMail($user);
        $rendered = $mailable->render();

        $this->assertEquals('Configure sua Senha Local', $mailable->subject);
        $this->assertStringContainsString('Olá!', $rendered);
        $this->assertStringContainsString('Você solicitou a configuração de uma senha local', $rendered);
        $this->assertStringContainsString('Clique no botão abaixo para definir sua senha', $rendered);

        $this->assertStringContainsString($pathAndQuery, $rendered);

        $this->assertEquals('Definir Senha Local', $mailable->actionText);
        $this->assertEquals($expectedSignedUrl, $mailable->actionUrl);
        $this->assertStringContainsString('Se você não solicitou isso', $rendered);
        $this->assertStringContainsString('Atenciosamente,', $rendered);
    }
}