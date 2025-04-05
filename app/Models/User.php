<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyUserEmail;
use App\Notifications\SendResetPasswordLink;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use \Spatie\Permission\Traits\HasRoles;
    use \Uspdev\SenhaunicaSocialite\Traits\HasSenhaunica;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'codpes',
        'email_verified_at',
    ];

    /**
     * Os atributos que devem ser ocultados para serialização.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtém os atributos que devem ser convertidos para tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Envia a notificação de verificação de e-mail.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyUserEmail);
    }

    /**
     * Envia a notificação de redefinição de senha.
     *
     * @param string $token O token de redefinição de senha.
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new SendResetPasswordLink($token));
    }
}