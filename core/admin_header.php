<?php
require_once __DIR__ . '/db_connect.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <title>CMS - ShiftsHappen</title>
</head>

<body class="admin-body">
    <header class="admin-header">
        <div class="admin-header__brand">
            <span class="admin-header__logo">SH</span>
            <p>Welkom, <?= testInput($_SESSION['gebruikersnaam']) ?></p>
        </div>
        <nav class="adminlink">
            <a href="<?= view('settings.php') ?>">Instellingen</a>
            <a href="<?= view('pages.php') ?>?slug=home" target="_blank">Website</a>
            <a href="<?= view('admin.php') ?>#paginas">Pagina's</a>
            <a href="<?= view('admin.php') ?>#socials">Socials</a>
            <a href="<?= view('admin.php') ?>#contactberichten">Berichten</a>
            <a href="<?= view('changeLoginInfo.php') ?>">Account</a>
            <a href="<?= view('logout.php') ?>">Uitloggen</a>
        </nav>
    </header>
    <main class="admin-main">
