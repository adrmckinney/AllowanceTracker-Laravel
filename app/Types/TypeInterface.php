<?php

namespace App\Types;

interface TypeInterface
{
    public function fill(array $attributes);
    public function toArray();
}
