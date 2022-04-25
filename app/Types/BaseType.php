<?php

namespace App\Types;

abstract class BaseType implements TypeInterface
{
    protected $fillable = [];
    protected $attributes = [];

    public function __construct(array $attributes)
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes)
    {
        $allowed = $this->attributes;
        foreach ($attributes as $key => $value) {
            if ($this->isAllowed($key)) {
                $allowed[$key] = $value;
            }
        }

        $this->attributes = $allowed;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function __get($key)
    {
        return get_array_key($key, $this->attributes);
    }
    public function remove($key)
    {
        unset($this->attributes[$key]);
    }

    public function toArray($constraints = [])
    {
        if (array_key_exists('excludes', $constraints)) {
            $excludes = $constraints['excludes'];
            return array_filter(
                $this->attributes,
                function ($key) use ($excludes) {
                    foreach ($excludes as $exclude) {
                        if ($exclude === $key) {
                            return false;
                        }
                    }
                    return true;
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $this->attributes;
    }

    public function keyExists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    public function isMissingKey($key)
    {
        return !$this->keyExists($key);
    }

    protected function isAllowed($key)
    {
        if ($this->fillable === null) {
            return true;
        }
        foreach ($this->fillable as $item) {
            if ($item === $key) {
                return true;
            }
        }
        return false;
    }
}
