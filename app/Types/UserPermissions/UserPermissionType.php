<?php

namespace App\Types\Users;

use App\Types\BaseType;

class UserPermissionType extends BaseType
{
    protected $fillable = [
        'user_id',
        'permission_id',
    ];
}
