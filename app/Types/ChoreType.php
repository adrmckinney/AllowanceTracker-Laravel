<?php

namespace App\Types;

use App\Types\BaseType;

class ChoreType extends BaseType
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'cost'
    ];

    public function toCreateArray(): array
    {
        return $this->toArray([
            'id',
            'name',
            'description',
            'cost'
        ]);
    }
}
