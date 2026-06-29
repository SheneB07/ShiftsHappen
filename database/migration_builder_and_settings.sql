-- Run once in phpMyAdmin if you already have shiftshappen imported.

USE `shiftshappen`;

ALTER TABLE `paginainfo`
  ADD COLUMN `backgroundKleur` VARCHAR(20) NOT NULL DEFAULT '#f9fafb' AFTER `backgroundColor`;

UPDATE `paginainfo` SET `backgroundKleur` = '#dbeafe' WHERE `backgroundColor` = 1 AND `foto` = 0;
UPDATE `paginainfo` SET `backgroundKleur` = '#f9fafb' WHERE `backgroundColor` = 0 AND `foto` = 0;

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `header_title` VARCHAR(191) NOT NULL DEFAULT 'ShiftsHappen',
  `header_logo` VARCHAR(255) NOT NULL DEFAULT 'images/ShiftHappens-logo.png',
  `header_nav` VARCHAR(255) NOT NULL DEFAULT '',
  `header_bg` VARCHAR(20) NOT NULL DEFAULT '#111827',
  `header_text` VARCHAR(20) NOT NULL DEFAULT '#f9fafb',
  `header_link` VARCHAR(20) NOT NULL DEFAULT '#dbeafe',
  `body_bg` VARCHAR(20) NOT NULL DEFAULT '#f3f4f6',
  `page_bg` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
  `accent_color` VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  `footer_bg` VARCHAR(20) NOT NULL DEFAULT '#111827',
  `footer_text` VARCHAR(20) NOT NULL DEFAULT '#9ca3af',
  `cookie_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `cookie_tekst` TEXT NOT NULL,
  `cookie_button_text` VARCHAR(100) NOT NULL DEFAULT 'Accepteren',
  `cookie_bg` VARCHAR(20) NOT NULL DEFAULT '#111827',
  `cookie_text_color` VARCHAR(20) NOT NULL DEFAULT '#f9fafb',
  `cookie_button_bg` VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  `cookie_button_text_color` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
  `footer_html` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (
  `id`, `header_title`, `header_logo`, `header_nav`, `cookie_tekst`, `footer_html`
) VALUES (
  1,
  'ShiftsHappen',
  'images/ShiftHappens-logo.png',
  '',
  'We gebruiken cookies om je ervaring op onze website te verbeteren. Door op Accepteren te klikken ga je akkoord met ons cookiebeleid.',
  CONCAT('<p>© Shifts happen ', YEAR(CURDATE()), ' – Alle rechten voorbehouden</p>')
) ON DUPLICATE KEY UPDATE `id` = `id`;
