<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'provider',
        'provider_id',
    ];

    protected $guarded = ['image','password'];
    protected $hidden=['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userProjects(){
        return $this->HasMany(UserProject::class);
    }

    public function assignments(){
        return $this->HasMany(Assignment::class);
    }
}
