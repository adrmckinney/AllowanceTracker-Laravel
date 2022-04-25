<?php

namespace App\Types\Users;

use App\Types\BaseType;

class UpdateUserType extends BaseType
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'permissions',
    ];
}
