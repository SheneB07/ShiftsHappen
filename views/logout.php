<?php
require_once __DIR__ . '/../core/db_connect.php';

$_SESSION = [];
session_destroy();

header('Location: ' . view('login.php'));
exit();
