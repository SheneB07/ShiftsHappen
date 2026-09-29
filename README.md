# ShiftsHappen CMS

Een eenvoudig PHP CMS met login, registratie en een admin paneel voor pagina's, social links en contactberichten.

## Setup (XAMPP)

1. **Database importeren**
   - Start Apache + MySQL in XAMPP
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Ga naar **Import** en kies `database/shiftshappen.sql`
   - Klik op **Go**

2. **Database credentials**
   - Standaard staat `core/db_credentials.php` al op XAMPP defaults (`root` / leeg wachtwoord)
   - Pas aan als jouw MySQL setup anders is (zie ook `core/db_credentials.example.php`)

3. **Site openen**
   - Publieke site: http://localhost/ShiftsHappen/
   - Admin login: http://localhost/ShiftsHappen/views/login.php

## Pagina's & contactformulier

- Publieke pagina's staan op `views/pages.php?slug=home` (of een andere slug)
- In het admin paneel kun je bij elke pagina **Contactformulier op deze pagina** aanvinken
- Berichten komen binnen onder **Berichten** in het admin paneel
- Pagina's met grid-layout gebruiken de tabellen `paginagrid` en `paginainfo` (inbegrepen in de SQL)

### Al eerder geïmporteerd?

- `database/migration_grid_and_contact.sql` — grid tabellen + contactformulier preset
- `database/migration_builder_and_settings.sql` — layout editor, kleuren per kolom, cookie popup
- `database/migration_page_theme.sql` — per-pagina kleuren
- `database/migration_layout_options.sql` — uitlijning, breedte, randen, flush kolommen

## Layout editor & instellingen

- **Layout bewerken** — drag-and-drop rijen, live preview, opslaan zonder page reload
- **Pagina kleuren** — per pagina in layout editor (header blijft globaal via Instellingen)
- **Instellingen** — globale header, accentkleur en cookie popup

## Standaard login

| Gebruikersnaam | Wachtwoord |
|----------------|------------|
| `admin`        | `admin123` |

Wijzig dit wachtwoord na de eerste login via **Account** in het admin paneel.

## Structuur

- `core/` — database connectie, config, admin layout
- `views/` — login, registratie, admin pagina's
- `assets/` — CSS en JS
- `database/shiftshappen.sql` — volledige database schema + voorbeelddata

## Admin functies

- **Pagina's** — CMS pagina's toevoegen, bewerken en verwijderen
- **Socials** — social media links beheren
- **Berichten** — contactberichten bekijken, markeren als gelezen, verwijderen
- **Account** — gebruikersnaam en wachtwoord wijzigen
