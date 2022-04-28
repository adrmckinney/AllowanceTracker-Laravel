<?php

namespace App\Types\Users;

use App\Types\BaseType;

class UserType extends BaseType
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'permissions',
    ];
}
