<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public static $ROLE_USER = 'user';
    public static $ROLE_AUTHOR = 'autor';
    public static $ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'lastname',
        'second_lastname',
        'email',
        'password',
        'avatar',
    ];

    public static $rules = [
        'name' => 'required',
        'lastname' => 'required',
        'second_lastname' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
        'password_confirmation' => 'required|min:8',
        'avatar' => '',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function articles() {
        return $this->belongsToMany(Article::class);
    }
}
