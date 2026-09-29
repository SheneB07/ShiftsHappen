<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_credentials.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$con = new mysqli($dbhost, $dbusername, $dbpassword, $dbname);

if ($con->connect_errno) {
    die('Failed to connect to MySQL: ' . $con->connect_error);
}

$con->set_charset('utf8mb4');

function testInput(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function requireLogin(): void
{
    if (!isset($_SESSION['gebruikersnaam'])) {
        header('Location: ' . view('login.php'));
        exit();
    }
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit();
}

function validateHexColor(string $color, string $default = '#111827'): string
{
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : $default;
}
