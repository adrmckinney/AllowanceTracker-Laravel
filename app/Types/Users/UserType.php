<?php

namespace App\Types\Users;

use App\Types\BaseType;

class UserType extends BaseType
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'api_token',
        'permissions',
    ];

    public function toCreateArray(): array
    {
        return $this->toArray(['exclude' => [
            'id'
        ]]);
    }
}
