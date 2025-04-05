<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendLocalPasswordLink;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission as SpatiePermission;

class UspLocalPasswordTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Configura o ambiente de teste antes de cada teste.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'usp_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'external_user', 'guard_name' => 'web']);

        SpatiePermission::firstOrCreate(['name' => 'senhaunica', 'guard_name' => 'web']);
    }

    /**
     * Testa se o formulário de solicitação de senha local pode ser renderizado.
     *
     * @return void
     */
    public function test_request_local_password_form_can_be_rendered(): void
    {
        $response = $this->get(route('local-password.request'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.request-local-password');
    }

    /**
     * Testa se o link de senha local pode ser solicitado para um usuário USP válido.
     *
     * @return void
     */
    public function test_local_password_link_can_be_requested_for_valid_usp_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@usp.br',
            'codpes' => '1234567',
        ]);
        $user->assignRole('usp_user');

        $response = $this->post(route('local-password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, SendLocalPasswordLink::class);
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $response->assertSessionHas('status', __('Se um usuário USP válido existir com este email, um link para definir senha local será enviado!'));
    }

    /**
     * Testa se a solicitação de link de senha local falha para e-mail não-USP.
     *
     * @return void
     */
    public function test_local_password_link_request_fails_for_non_usp_email(): void
    {
        Notification::fake();

        $response = $this->post(route('local-password.request'), ['email' => 'test@example.com']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');

        $response->assertSessionHasErrors(['email' => 'Por favor, forneça um email usp.br válido.']);
    }

    /**
     * Testa se a solicitação de link de senha local falha para usuário inexistente.
     *
     * @return void
     */
    public function test_local_password_link_request_fails_for_non_existent_user(): void
    {
        Notification::fake();

        $response = $this->post(route('local-password.request'), ['email' => 'nonexistent@usp.br']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');

        $response->assertSessionHasErrors(['email' => 'Nenhum usuário USP encontrado com este email.']);
    }

    /**
     * Testa se a solicitação de link de senha local falha para usuário sem CodPes.
     *
     * @return void
     */
    public function test_local_password_link_request_fails_for_user_without_codpes(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'nocodpes@usp.br',
            'codpes' => null,
        ]);
        $user->assignRole('external_user');

        $response = $this->post(route('local-password.request'), ['email' => $user->email]);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');

        $response->assertSessionHasErrors(['email' => 'Este email não pertence a um usuário USP válido no sistema.']);
    }

    /**
     * Testa se o formulário de definição de senha local pode ser renderizado com assinatura válida.
     *
     * @return void
     */
    public function test_set_local_password_form_can_be_rendered_with_valid_signature(): void
    {
        $user = User::factory()->create(['email' => 'test@usp.br', 'codpes' => '123']);

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertViewIs('auth.set-local-password');
        $response->assertViewHas('email', $user->email);
        $response->assertSee('Defina sua nova senha local');
    }

    /**
     * Testa se o formulário de definição de senha local falha com assinatura inválida.
     *
     * @return void
     */
    public function test_set_local_password_form_fails_with_invalid_signature(): void
    {
        $user = User::factory()->create(['email' => 'test@usp.br', 'codpes' => '123']);
        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );
        $invalidUrl = str_replace('signature=', 'signature=invalid', $signedUrl);

        $response = $this->get($invalidUrl);

        $response->assertStatus(403);
    }

    /**
     * Testa se a senha local pode ser definida com dados válidos.
     *
     * @return void
     */
    public function test_local_password_can_be_set_with_valid_data(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@usp.br',
            'codpes' => '1234567',
            'password' => null,
        ]);
         $user->assignRole('usp_user');

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );

        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $response = $this->post(route('local-password.set'), [
            'email' => $user->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
            'password' => 'new-local-password',
            'password_confirmation' => 'new-local-password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Senha local definida com sucesso!');
        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('new-local-password', $user->password));
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Testa se a definição de senha local falha com senhas não coincidentes.
     *
     * @return void
     */
    public function test_set_local_password_fails_with_password_mismatch(): void
    {
        $user = User::factory()->create([
            'email' => 'test@usp.br',
            'codpes' => '1234567',
            'password' => Hash::make('old-password'),
        ]);
        $user->assignRole('usp_user');
        $user->givePermissionTo('senhaunica');

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );
        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $response = $this->from(route('local-password.set', $queryParams))->post(route('local-password.set'), [
            'email' => $user->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
            'password' => 'new-local-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect(route('local-password.set', $queryParams));
        $response->assertSessionHasErrors('password');
        $this->assertGuest();

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /**
     * Testa se a definição de senha local falha para usuário não-USP no POST.
     *
     * @return void
     */
     public function test_set_local_password_fails_for_non_usp_user_on_post(): void
     {
         $user = User::factory()->create([
             'email' => 'external@example.com',
             'codpes' => null,
             'password' => null
         ]);
         $user->assignRole('external_user');

         $signedUrl = URL::temporarySignedRoute(
             'local-password.set',
             now()->addHour(),
             ['email' => $user->email]
         );
         parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

         $response = $this->post(route('local-password.set'), [
             'email' => $user->email,
             'expires' => $queryParams['expires'],
             'signature' => $queryParams['signature'],
             'password' => 'new-local-password',
             'password_confirmation' => 'new-local-password',
         ]);

         $response->assertSessionHasErrors('email');
         $response->assertSessionHasErrors(['email' => 'Usuário não encontrado ou inválido.']);
         $this->assertGuest();
         $this->assertNull($user->fresh()->password);
     }
}