<?php

function defaultSiteSettings(): array
{
    return [
        'id' => 1,
        'header_bg' => '#fafafa',
        'header_text' => '#f9fafb',
        'header_link' => '#dbeafe',
        'body_bg' => '#f3f4f6',
        'page_bg' => '#ffffff',
        'accent_color' => '#2563eb',
        'footer_bg' => '#f1f3f7',
        'footer_text' => '#9ca3af',
        'footer_title' => 'ShiftsHappen',
        'footer_content' => '© ' . date('Y') . ' ShiftsHappen',
        'cookie_enabled' => 1,
        'cookie_tekst' => 'We gebruiken cookies om je ervaring op onze website te verbeteren. Door op Accepteren te klikken ga je akkoord met ons cookiebeleid.',
        'cookie_button_text' => 'Accepteren',
        'cookie_bg' => '#111827',
        'cookie_text_color' => '#f9fafb',
        'cookie_button_bg' => '#2563eb',
        'cookie_button_text_color' => '#ffffff',
    ];
}

function getSiteSettings(mysqli $con): array
{
    $result = $con->query('SELECT * FROM site_settings WHERE id = 1');

    if ($result && $row = $result->fetch_assoc()) {
        return array_merge(defaultSiteSettings(), $row);
    }

    return defaultSiteSettings();
}

function saveSiteSettings(mysqli $con, array $data): bool
{
    $defaults = defaultSiteSettings();
    $settings = array_merge($defaults, $data);

    $stmt = $con->prepare(
        'INSERT INTO site_settings (
            id, header_bg, header_text, header_link, footer_title, footer_content, body_bg, page_bg, accent_color,
            footer_bg, footer_text, cookie_enabled, cookie_tekst, cookie_button_text,
            cookie_bg, cookie_text_color, cookie_button_bg, cookie_button_text_color
        ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            header_bg = VALUES(header_bg),
            header_text = VALUES(header_text),
            header_link = VALUES(header_link),
            body_bg = VALUES(body_bg),
            page_bg = VALUES(page_bg),
            accent_color = VALUES(accent_color),
            footer_bg = VALUES(footer_bg),
            footer_text = VALUES(footer_text),
            footer_title = VALUES(footer_title),
            footer_content = VALUES(footer_content),
            cookie_enabled = VALUES(cookie_enabled),
            cookie_tekst = VALUES(cookie_tekst),
            cookie_button_text = VALUES(cookie_button_text),
            cookie_bg = VALUES(cookie_bg),
            cookie_text_color = VALUES(cookie_text_color),
            cookie_button_bg = VALUES(cookie_button_bg),
            cookie_button_text_color = VALUES(cookie_button_text_color)'
    );

    $cookieEnabled = !empty($settings['cookie_enabled']) ? 1 : 0;

    $stmt->bind_param(
        'ssssssssssissssss',
        $settings['header_bg'],
        $settings['header_text'],
        $settings['header_link'],
        $settings['body_bg'],
        $settings['page_bg'],
        $settings['accent_color'],
        $settings['footer_bg'],
        $settings['footer_text'],
        $settings['footer_title'],
        $settings['footer_content'],
        $cookieEnabled,
        $settings['cookie_tekst'],
        $settings['cookie_button_text'],
        $settings['cookie_bg'],
        $settings['cookie_text_color'],
        $settings['cookie_button_bg'],
        $settings['cookie_button_text_color']
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
