<?php

function _log($message, $context = [], $type = 'info')
{
    \Illuminate\Support\Facades\Log::$type($message, $context);
}
