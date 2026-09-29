<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/site_settings.php';

$siteSettings = getSiteSettings($con);
$pageTheme = $pageTheme ?? null;
$navPages = $con->query("SELECT id, titel, slug FROM paginas WHERE slug <> 'footer' ORDER BY titel ASC");
$socialLinks = $con->query('SELECT platform, url FROM socials ORDER BY volgorde ASC, platform ASC');
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public.css') ?>">
    <style>
        :root {
            --header-bg: <?= testInput($siteSettings['header_bg']) ?>;
            --header-text: <?= testInput($siteSettings['header_text']) ?>;
            --header-link: <?= testInput($siteSettings['header_link']) ?>;
            --body-bg: <?= testInput($pageTheme['body_bg'] ?? $siteSettings['body_bg']) ?>;
            --page-bg: <?= testInput($pageTheme['page_bg'] ?? $siteSettings['page_bg']) ?>;
            --accent-color: <?= testInput($siteSettings['accent_color']) ?>;
            --footer-bg: <?= testInput($pageTheme['footer_bg'] ?? $siteSettings['footer_bg']) ?>;
            --footer-text: <?= testInput($pageTheme['footer_text'] ?? $siteSettings['footer_text']) ?>;
            --page-text-color: <?= testInput($pageTheme['page_text_color'] ?? '#111827') ?>;
        }
    </style>
    <title><?= isset($pageTitle) ? testInput($pageTitle) . ' - ' : '' ?>ShiftsHappen</title>
</head>

<body class="public-body">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-logo" href="<?= view('pages.php') ?>?slug=home">
                <span class="site-logo__mark">SH</span>
                <span class="site-logo__text">ShiftsHappen</span>
            </a>

            <nav class="site-nav">
                <?php if ($navPages): ?>
                    <?php while ($navPage = $navPages->fetch_assoc()): ?>
                        <a href="<?= view('pages.php') ?>?slug=<?= urlencode($navPage['slug']) ?>">
                            <?= testInput($navPage['titel']) ?>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </nav>

            <?php if ($socialLinks && $socialLinks->num_rows > 0): ?>
                <div class="site-socials">
                    <?php while ($social = $socialLinks->fetch_assoc()): ?>
                        <a href="<?= testInput($social['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= testInput($social['platform']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="site-main">
