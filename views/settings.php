<?php
require_once __DIR__ . '/../core/admin_header.php';
require_once __DIR__ . '/../core/site_settings.php';

$notice = '';
$noticeError = false;
$settings = getSiteSettings($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'header_title' => trim($_POST['header_title'] ?? $settings['header_title']),
        'header_nav' => trim($_POST['header_nav'] ?? $settings['header_nav'] ?? ''),
        'header_bg' => validateHexColor($_POST['header_bg'] ?? '#111827'),
        'header_text' => validateHexColor($_POST['header_text'] ?? '#f9fafb'),
        'header_link' => validateHexColor($_POST['header_link'] ?? '#dbeafe'),
        'body_bg' => $settings['body_bg'],
        'page_bg' => $settings['page_bg'],
        'accent_color' => validateHexColor($_POST['accent_color'] ?? '#2563eb'),
        'footer_bg' => validateHexColor($_POST['footer_bg'] ?? $settings['footer_bg']),
        'footer_text' => validateHexColor($_POST['footer_text'] ?? $settings['footer_text']),
        'cookie_enabled' => isset($_POST['cookie_enabled']) ? 1 : 0,
        'cookie_tekst' => trim($_POST['cookie_tekst'] ?? ''),
        'cookie_button_text' => trim($_POST['cookie_button_text'] ?? 'Accepteren'),
        'cookie_bg' => validateHexColor($_POST['cookie_bg'] ?? '#111827'),
        'cookie_text_color' => validateHexColor($_POST['cookie_text_color'] ?? '#f9fafb'),
        'cookie_button_bg' => validateHexColor($_POST['cookie_button_bg'] ?? '#2563eb'),
        'cookie_button_text_color' => validateHexColor($_POST['cookie_button_text_color'] ?? '#ffffff'),
        'footer_html' => trim($_POST['footer_html'] ?? $settings['footer_html']),
    ];

    // header logo upload removed — header uses text nav by default

    // Handle uploaded logo file (optional)
    if (!empty($_FILES['header_logo_file']) && !empty($_FILES['header_logo_file']['tmp_name'])) {
        $file = $_FILES['header_logo_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $maxSize = 2 * 1024 * 1024; // 2MB
            $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $notice = 'Alleen PNG/JPEG/GIF/WebP afbeeldingen zijn toegestaan.';
                $noticeError = true;
            } elseif ($file['size'] > $maxSize) {
                $notice = 'Afbeelding is te groot (max 2MB).';
                $noticeError = true;
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newName = 'images/logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = __DIR__ . '/../' . $newName;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $data['header_logo'] = $newName;
                } else {
                    $notice = 'Upload mislukt bij verplaatsen.';
                    $noticeError = true;
                }
            }
        } else {
            $notice = 'Fout bij uploaden.';
            $noticeError = true;
        }
    }

    if ($data['cookie_tekst'] === '') {
        $notice = 'Cookie-tekst mag niet leeg zijn.';
        $noticeError = true;
    } elseif (saveSiteSettings($con, $data)) {
        $settings = getSiteSettings($con);
        $notice = 'Instellingen opgeslagen.';
    } else {
        $notice = 'Opslaan mislukt.';
        $noticeError = true;
    }
}
?>

<div class="admin-panel">
    <h1>Website instellingen</h1>
    <p>Pas de globale header, accentkleur en cookie-popup aan. Pagina-kleuren stel je per pagina in via <strong>Layout bewerken</strong>.</p>

    <?php if ($notice !== ''): ?>
        <div class="pop-up <?= $noticeError ? 'pop-up--error' : 'pop-up--success' ?>">
            <p><?= testInput($notice) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="admin-form" enctype="multipart/form-data">
        <section class="settings-block">
            <h2>Header content</h2>
            <div class="inputField">
                <label for="header_title">Header titel</label>
                <input type="text" name="header_title" id="header_title" value="<?= testInput($settings['header_title']) ?>">
            </div>
            <div class="inputField">
                <label for="header_nav">Header links (comma-separated slugs)</label>
                <input type="text" name="header_nav" id="header_nav" value="<?= testInput($settings['header_nav'] ?? '') ?>" placeholder="home,aanbod,portfolio,contact">
                <small>Gebruik slug-waarden van pagina's, gescheiden door komma's. Leeg = alle pagina's.</small>
            </div>
            <div class="inputField">
                <label for="header_logo">Header logo (pad naar afbeelding)</label>
                <input type="text" name="header_logo" id="header_logo" value="<?= testInput($settings['header_logo'] ?? '') ?>" placeholder="images/logo.png">
            </div>
            <div class="inputField">
                <label for="header_logo_file">Of upload logo</label>
                <input type="file" name="header_logo_file" id="header_logo_file" accept="image/*">
                <?php if (!empty($settings['header_logo'])): ?>
                    <div class="image-preview">
                        <p>Huidige logo:</p>
                        <img src="<?= asset(testInput($settings['header_logo'])) ?>" alt="Logo" style="max-height:60px;">
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section class="settings-block">
            <h2>Header & accent (globaal)</h2>
            <div class="color-grid">
                <div class="inputField">
                    <label>Header achtergrond</label>
                    <input type="color" name="header_bg" value="<?= testInput($settings['header_bg']) ?>">
                </div>
                <div class="inputField">
                    <label>Header tekst</label>
                    <input type="color" name="header_text" value="<?= testInput($settings['header_text']) ?>">
                </div>
                <div class="inputField">
                    <label>Header links</label>
                    <input type="color" name="header_link" value="<?= testInput($settings['header_link']) ?>">
                </div>
                <div class="inputField">
                    <label>Accentkleur (logo)</label>
                    <input type="color" name="accent_color" value="<?= testInput($settings['accent_color']) ?>">
                </div>
            </div>
        </section>

        <section class="settings-block">
            <h2>Cookie popup</h2>
            <div class="inputField inputField--checkbox">
                <label>
                    <input type="checkbox" name="cookie_enabled" value="1" <?= !empty($settings['cookie_enabled']) ? 'checked' : '' ?>>
                    Cookie popup tonen
                </label>
            </div>
            <div class="inputField">
                <label for="cookie_tekst">Tekst</label>
                <textarea name="cookie_tekst" id="cookie_tekst" rows="3" required><?= testInput($settings['cookie_tekst']) ?></textarea>
            </div>
            <div class="inputField">
                <label for="cookie_button_text">Knoptekst</label>
                <input type="text" name="cookie_button_text" id="cookie_button_text" value="<?= testInput($settings['cookie_button_text']) ?>" required>
            </div>
            <div class="color-grid">
                <div class="inputField">
                    <label>Popup achtergrond</label>
                    <input type="color" name="cookie_bg" value="<?= testInput($settings['cookie_bg']) ?>">
                </div>
                <div class="inputField">
                    <label>Popup tekst</label>
                    <input type="color" name="cookie_text_color" value="<?= testInput($settings['cookie_text_color']) ?>">
                </div>
                <div class="inputField">
                    <label>Knop achtergrond</label>
                    <input type="color" name="cookie_button_bg" value="<?= testInput($settings['cookie_button_bg']) ?>">
                </div>
                <div class="inputField">
                    <label>Knop tekst</label>
                    <input type="color" name="cookie_button_text_color" value="<?= testInput($settings['cookie_button_text_color']) ?>">
                </div>
            </div>
        </section>

        <section class="settings-block">
            <h2>Footer content</h2>
            <div class="inputField">
                <label for="footer_html">Footer HTML / tekst</label>
                <textarea name="footer_html" id="footer_html" rows="4"><?= testInput($settings['footer_html']) ?></textarea>
                <small>Je kunt HTML gebruiken voor extra opmaak of links.</small>
            </div>
            <div class="color-grid">
                <div class="inputField">
                    <label>Footer achtergrond</label>
                    <input type="color" name="footer_bg" value="<?= testInput($settings['footer_bg']) ?>">
                </div>
                <div class="inputField">
                    <label>Footer tekst</label>
                    <input type="color" name="footer_text" value="<?= testInput($settings['footer_text']) ?>">
                </div>
            </div>
        </section>

        <button type="submit">Instellingen opslaan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../core/admin_footer.php'; ?>
