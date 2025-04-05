<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\VerifyUserEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase; // Adicionar se necessário

class VerifyUserEmailTest extends TestCase
{
    use RefreshDatabase; // Adicionar se User::factory() for usado

    protected $seed = true; // Adicionar se roles/permissions forem relevantes

    public function test_verify_user_email_mail_content(): void
    {
        config(['app.url' => 'http://localhost']);

        $user = User::factory()->make([
            'id' => 1,
            'email' => 'test@example.com',
        ]);
        $userHash = sha1($user->getEmailForVerification());

        $notification = new VerifyUserEmail();

        $expectedSignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(1),
            [
                'id' => $user->getKey(),
                'hash' => $userHash,
            ]
        );

        // Obter apenas a parte do path e query *antes* da assinatura
        $urlParts = parse_url($expectedSignedUrl);
        $pathAndQuery = $urlParts['path'] . '?' . explode('&signature=', $urlParts['query'])[0]; // Pega tudo antes da assinatura

        $mailable = $notification->toMail($user);
        $rendered = $mailable->render();

        $this->assertEquals('Verifique seu Endereço de Email', $mailable->subject);
        $this->assertStringContainsString('Olá!', $rendered);
        $this->assertStringContainsString('Clique no botão abaixo para verificar seu endereço de email.', $rendered);

        // --- CORREÇÃO REFINADA ---
        // Verificar se o path e os parâmetros essenciais (sem expires/signature) estão no corpo
        $this->assertStringContainsString($pathAndQuery, $rendered);
         // --- FIM DA CORREÇÃO REFINADA ---

        $this->assertEquals('Verificar Email', $mailable->actionText);
        $this->assertEquals($expectedSignedUrl, $mailable->actionUrl);
        $this->assertStringContainsString('Este link expirará em 60 minutos.', $rendered);
    }
}