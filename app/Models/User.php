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

    protected $fillable = [
        'name',
        'email',
        'password',
        'codpes', 
        'email_verified_at', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyUserEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new SendResetPasswordLink($token));
    }
}
