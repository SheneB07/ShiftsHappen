-- ShiftsHappen CMS database
-- Import this file in phpMyAdmin (Import tab) or run via mysql CLI.

CREATE DATABASE IF NOT EXISTS `shiftshappen`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `shiftshappen`;

-- Login accounts for the admin panel
CREATE TABLE IF NOT EXISTS `logininfo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gebruikersnaam` VARCHAR(100) NOT NULL,
  `wachtwoord` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gebruikersnaam` (`gebruikersnaam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin account: username "admin", password "admin123"
INSERT INTO `logininfo` (`gebruikersnaam`, `wachtwoord`) VALUES
('admin', '$2y$10$ZdeeIpPCW/TQlr2ezKFkDuTiNTXSbhrJ/6bDPbwkuWZbPXnixzGzK');

-- CMS pages
CREATE TABLE IF NOT EXISTS `paginas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titel` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `inhoud` TEXT NOT NULL,
  `heeft_contactformulier` TINYINT(1) NOT NULL DEFAULT 0,
  `body_bg` VARCHAR(20) DEFAULT NULL,
  `page_bg` VARCHAR(20) DEFAULT NULL,
  `page_text_color` VARCHAR(20) DEFAULT NULL,
  `footer_bg` VARCHAR(20) DEFAULT NULL,
  `footer_text` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `paginas` (`titel`, `slug`, `inhoud`, `heeft_contactformulier`) VALUES
('Home', 'home', 'Welkom bij ShiftsHappen! Dit is de standaard homepagina.', 0),
('Over ons', 'over-ons', 'Vertel hier iets over je organisatie.', 0),
('Contact', 'contact', 'Heb je een vraag? Vul het formulier hieronder in.', 1);

-- Grid layout rows for public pages (optional per page)
CREATE TABLE IF NOT EXISTS `paginagrid` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageValue` INT UNSIGNED NOT NULL,
  `rowPosition` INT NOT NULL DEFAULT 0,
  `columnType` TINYINT NOT NULL DEFAULT 1,
  `row_width_pct` TINYINT UNSIGNED NOT NULL DEFAULT 100,
  `row_align` VARCHAR(10) NOT NULL DEFAULT 'left',
  `column_gap` SMALLINT UNSIGNED NOT NULL DEFAULT 16,
  `flush_columns` TINYINT(1) NOT NULL DEFAULT 0,
  `border_top` TINYINT(1) NOT NULL DEFAULT 0,
  `border_right` TINYINT(1) NOT NULL DEFAULT 0,
  `border_bottom` TINYINT(1) NOT NULL DEFAULT 0,
  `border_left` TINYINT(1) NOT NULL DEFAULT 0,
  `border_width` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `border_color` VARCHAR(20) NOT NULL DEFAULT '#d1d5db',
  PRIMARY KEY (`id`),
  KEY `pageValue` (`pageValue`),
  CONSTRAINT `paginagrid_page_fk` FOREIGN KEY (`pageValue`) REFERENCES `paginas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `paginainfo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `whichRow` INT UNSIGNED NOT NULL,
  `colum` TINYINT NOT NULL,
  `informatie` TEXT NOT NULL,
  `foto` TINYINT(1) NOT NULL DEFAULT 0,
  `backgroundColor` TINYINT(1) NOT NULL DEFAULT 0,
  `bold` TINYINT(1) NOT NULL DEFAULT 0,
  `italic` TINYINT(1) NOT NULL DEFAULT 0,
  `opacity` TINYINT NOT NULL DEFAULT 10,
  `kleur` VARCHAR(20) NOT NULL DEFAULT '#111827',
  `backgroundKleur` VARCHAR(20) NOT NULL DEFAULT '#f9fafb',
  `text_align` VARCHAR(10) NOT NULL DEFAULT 'left',
  `vertical_align` VARCHAR(10) NOT NULL DEFAULT 'top',
  `width_pct` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `padding_px` TINYINT UNSIGNED NOT NULL DEFAULT 16,
  `border_top` TINYINT(1) NOT NULL DEFAULT 0,
  `border_right` TINYINT(1) NOT NULL DEFAULT 0,
  `border_bottom` TINYINT(1) NOT NULL DEFAULT 0,
  `border_left` TINYINT(1) NOT NULL DEFAULT 0,
  `border_width` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `border_color` VARCHAR(20) NOT NULL DEFAULT '#d1d5db',
  PRIMARY KEY (`id`),
  KEY `whichRow` (`whichRow`),
  CONSTRAINT `paginainfo_row_fk` FOREIGN KEY (`whichRow`) REFERENCES `paginagrid` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample grid content for the Home page
INSERT INTO `paginagrid` (`pageValue`, `rowPosition`, `columnType`) VALUES
(1, 1, 1),
(1, 2, 2);

INSERT INTO `paginainfo` (`whichRow`, `colum`, `informatie`, `foto`, `backgroundColor`, `backgroundKleur`, `bold`, `italic`, `opacity`, `kleur`) VALUES
(1, 1, 'Welkom bij ShiftsHappen! Wij helpen je met shifts die echt gebeuren.', 0, 1, '#dbeafe', 1, 0, 10, '#111827'),
(2, 1, 'Onze missie is om planning simpel en overzichtelijk te maken voor iedereen.', 0, 0, '#f9fafb', 0, 0, 10, '#111827'),
(2, 2, 'Neem contact op via de contactpagina als je vragen hebt.', 0, 0, '#f9fafb', 0, 1, 10, '#1d4ed8');

-- Site-wide theme and cookie popup settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`id`, `cookie_tekst`) VALUES
(1, 'We gebruiken cookies om je ervaring op onze website te verbeteren. Door op Accepteren te klikken ga je akkoord met ons cookiebeleid.');

-- Social media links
CREATE TABLE IF NOT EXISTS `socials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `volgorde` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `socials` (`platform`, `url`, `volgorde`) VALUES
('Instagram', 'https://instagram.com/', 1),
('LinkedIn', 'https://linkedin.com/', 2);

-- Contact form messages
CREATE TABLE IF NOT EXISTS `contactberichten` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naam` VARCHAR(150) NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `bericht` TEXT NOT NULL,
  `gelezen` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contactberichten` (`naam`, `email`, `bericht`, `gelezen`) VALUES
('Test Gebruiker', 'test@example.com', 'Dit is een voorbeeld contactbericht.', 0);
