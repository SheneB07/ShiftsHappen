<?php

define('APP_ROOT', dirname(__DIR__));
define('BASE_PATH', '/ShiftsHappen');

function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function view(string $path): string
{
    return url('views/' . ltrim($path, '/'));
}
