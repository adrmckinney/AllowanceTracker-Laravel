<?php

function _log($message, $context = [], $type = 'info')
{
    \Illuminate\Support\Facades\Log::$type($message, $context);
}

function get_array_key($key, $array)
{
    return array_key_exists($key, $array) ? $array[$key] : null;
}
