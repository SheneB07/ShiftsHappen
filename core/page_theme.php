<?php

require_once __DIR__ . '/site_settings.php';

function getPageTheme(array $page, mysqli $con): array
{
    $site = getSiteSettings($con);

    return [
        'body_bg' => validateHexColor($page['body_bg'] ?? '', $site['body_bg']),
        'page_bg' => validateHexColor($page['page_bg'] ?? '', $site['page_bg']),
        'page_text_color' => validateHexColor($page['page_text_color'] ?? '', '#111827'),
        'footer_bg' => validateHexColor($page['footer_bg'] ?? '', $site['footer_bg']),
        'footer_text' => validateHexColor($page['footer_text'] ?? '', $site['footer_text']),
    ];
}

function savePageTheme(mysqli $con, int $pageId, array $colors): bool
{
    $bodyBg = validateHexColor($colors['body_bg'] ?? '#f3f4f6');
    $pageBg = validateHexColor($colors['page_bg'] ?? '#ffffff');
    $pageText = validateHexColor($colors['page_text_color'] ?? '#111827');
    $footerBg = validateHexColor($colors['footer_bg'] ?? '#111827');
    $footerText = validateHexColor($colors['footer_text'] ?? '#9ca3af');

    $stmt = $con->prepare(
        'UPDATE paginas SET body_bg = ?, page_bg = ?, page_text_color = ?, footer_bg = ?, footer_text = ? WHERE id = ?'
    );
    $stmt->bind_param('sssssi', $bodyBg, $pageBg, $pageText, $footerBg, $footerText, $pageId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
