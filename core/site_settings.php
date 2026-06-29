<?php

function defaultSiteSettings(): array
{
    return [
        'id' => 1,
        'header_title' => 'ShiftsHappen',
        'header_logo' => 'images/ShiftHappens-logo.png',
        'header_nav' => '',
        'header_bg' => '#111827',
        'header_text' => '#f9fafb',
        'header_link' => '#dbeafe',
        'body_bg' => '#f3f4f6',
        'page_bg' => '#ffffff',
        'accent_color' => '#2563eb',
        'footer_bg' => '#111827',
        'footer_text' => '#9ca3af',
        'cookie_enabled' => 1,
        'cookie_tekst' => 'We gebruiken cookies om je ervaring op onze website te verbeteren. Door op Accepteren te klikken ga je akkoord met ons cookiebeleid.',
        'cookie_button_text' => 'Accepteren',
        'cookie_bg' => '#111827',
        'cookie_text_color' => '#f9fafb',
        'cookie_button_bg' => '#2563eb',
        'cookie_button_text_color' => '#ffffff',
        'footer_html' => '<p>© Shifts happen ' . date('Y') . ' – Alle rechten voorbehouden</p>',
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
    // Ensure required columns exist so prepare() won't fail on older DBs
    ensureSiteSettingsColumns($con);

    $defaults = defaultSiteSettings();
    $settings = array_merge($defaults, $data);

    $stmt = $con->prepare(
        'INSERT INTO site_settings (
            id, header_title, header_logo, header_bg, header_text, header_link, header_nav,
            body_bg, page_bg, accent_color, footer_bg, footer_text, cookie_enabled,
            cookie_tekst, cookie_button_text, cookie_bg, cookie_text_color,
            cookie_button_bg, cookie_button_text_color, footer_html
        ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            header_title = VALUES(header_title),
            header_logo = VALUES(header_logo),
            header_nav = VALUES(header_nav),
            header_bg = VALUES(header_bg),
            header_text = VALUES(header_text),
            header_link = VALUES(header_link),
            body_bg = VALUES(body_bg),
            page_bg = VALUES(page_bg),
            accent_color = VALUES(accent_color),
            footer_bg = VALUES(footer_bg),
            footer_text = VALUES(footer_text),
            cookie_enabled = VALUES(cookie_enabled),
            cookie_tekst = VALUES(cookie_tekst),
            cookie_button_text = VALUES(cookie_button_text),
            cookie_bg = VALUES(cookie_bg),
            cookie_text_color = VALUES(cookie_text_color),
            cookie_button_bg = VALUES(cookie_button_bg),
            cookie_button_text_color = VALUES(cookie_button_text_color),
            footer_html = VALUES(footer_html)'
    );

    if ($stmt === false) {
        error_log('site_settings prepare failed: ' . $con->error);
        return false;
    }

    $cookieEnabled = !empty($settings['cookie_enabled']) ? 1 : 0;

    $stmt->bind_param(
        'ssssssssssssissssss',
        $settings['header_title'],
        $settings['header_logo'],
        $settings['header_bg'],
        $settings['header_text'],
        $settings['header_link'],
        $settings['header_nav'],
        $settings['body_bg'],
        $settings['page_bg'],
        $settings['accent_color'],
        $settings['footer_bg'],
        $settings['footer_text'],
        $cookieEnabled,
        $settings['cookie_tekst'],
        $settings['cookie_button_text'],
        $settings['cookie_bg'],
        $settings['cookie_text_color'],
        $settings['cookie_button_bg'],
        $settings['cookie_button_text_color'],
        $settings['footer_html']
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function ensureSiteSettingsColumns(mysqli $con): void
{
    $dbRow = $con->query("SELECT DATABASE() AS db");
    $dbName = $dbRow ? $dbRow->fetch_assoc()['db'] : null;
    if (!$dbName) {
        return;
    }

    $required = [
        'header_title' => "VARCHAR(191) NOT NULL DEFAULT 'ShiftsHappen'",
        'header_logo' => "VARCHAR(255) NOT NULL DEFAULT 'images/ShiftHappens-logo.png'",
        'header_nav' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'footer_html'  => "TEXT NOT NULL"
    ];

    $missing = [];
    foreach ($required as $col => $definition) {
        $q = $con->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'site_settings' AND COLUMN_NAME = ?");
        $q->bind_param('ss', $dbName, $col);
        $q->execute();
        $res = $q->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $q->close();
        if (!$row || (int) $row['cnt'] === 0) {
            $missing[$col] = $definition;
        }
    }

    if (empty($missing)) {
        return;
    }

    $parts = [];
    foreach ($missing as $col => $definition) {
        $parts[] = "ADD COLUMN `$col` $definition";
    }

    $sql = 'ALTER TABLE site_settings ' . implode(', ', $parts);
    $con->query($sql);

    // If footer_html was just added, populate a default value for id=1
    if (isset($missing['footer_html'])) {
        $con->query("UPDATE site_settings SET footer_html = CONCAT('<p>© Shifts happen ', YEAR(CURDATE()), ' – Alle rechten voorbehouden</p>') WHERE id = 1 AND (footer_html = '' OR footer_html IS NULL)");
    }
}
