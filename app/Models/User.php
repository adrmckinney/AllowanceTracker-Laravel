<?php

namespace App\Models;

use App\Data\Traits\UserAuthorizableTrait;
use App\Data\Traits\UserPermissionsTrait;
use App\Types\Users\UserType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use
        HasApiTokens,
        HasFactory,
        Notifiable,
        UserPermissionsTrait;

    public $timestamps = true;
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'api_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function chores(): HasMany
    // {
    //     return $this->hasMany(Chore::class, 'user_id');
    // }

    public function permissions(): HasMany
    {
        return $this->hasMany(UsersPermissions::class, 'user_id');
    }

    public function chores(): HasMany
    {
        return $this->hasMany(UserChore::class, 'user_id');
    }

    public function transactions(): MorphToMany
    {
        return $this->morphToMany(Transaction::class, 'transactionable');
    }
}
