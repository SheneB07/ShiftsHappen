<?php
require_once __DIR__ . '/../core/admin_header.php';

$notice = '';
$noticeError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_page') {
        $id = (int) ($_POST['id'] ?? 0);
        $titel = trim($_POST['titel'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $inhoud = trim($_POST['inhoud'] ?? '');
        $heeftContactformulier = isset($_POST['heeft_contactformulier']) ? 1 : 0;

        if ($titel === '' || $slug === '' || $inhoud === '') {
            $notice = 'Vul alle paginavelden in.';
            $noticeError = true;
        } elseif ($id > 0) {
            $stmt = $con->prepare('UPDATE paginas SET titel = ?, slug = ?, inhoud = ?, heeft_contactformulier = ? WHERE id = ?');
            $stmt->bind_param('sssii', $titel, $slug, $inhoud, $heeftContactformulier, $id);
            $stmt->execute();
            $notice = 'Pagina bijgewerkt.';
            $stmt->close();
        } else {
            $stmt = $con->prepare('INSERT INTO paginas (titel, slug, inhoud, heeft_contactformulier) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $titel, $slug, $inhoud, $heeftContactformulier);
            $stmt->execute();
            $notice = 'Pagina toegevoegd.';
            $stmt->close();
        }
    }

    if ($action === 'delete_page') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $con->prepare('DELETE FROM paginas WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $notice = 'Pagina verwijderd.';
        $stmt->close();
    }

    if ($action === 'save_social') {
        $id = (int) ($_POST['id'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $volgorde = (int) ($_POST['volgorde'] ?? 0);

        if ($platform === '' || $url === '') {
            $notice = 'Vul platform en URL in.';
            $noticeError = true;
        } elseif ($id > 0) {
            $stmt = $con->prepare('UPDATE socials SET platform = ?, url = ?, volgorde = ? WHERE id = ?');
            $stmt->bind_param('ssii', $platform, $url, $volgorde, $id);
            $stmt->execute();
            $notice = 'Social link bijgewerkt.';
            $stmt->close();
        } else {
            $stmt = $con->prepare('INSERT INTO socials (platform, url, volgorde) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $platform, $url, $volgorde);
            $stmt->execute();
            $notice = 'Social link toegevoegd.';
            $stmt->close();
        }
    }

    if ($action === 'delete_social') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $con->prepare('DELETE FROM socials WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $notice = 'Social link verwijderd.';
        $stmt->close();
    }

    if ($action === 'mark_read') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $con->prepare('UPDATE contactberichten SET gelezen = 1 WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $notice = 'Bericht gemarkeerd als gelezen.';
        $stmt->close();
    }

    if ($action === 'delete_message') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $con->prepare('DELETE FROM contactberichten WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $notice = 'Bericht verwijderd.';
        $stmt->close();
    }

    if ($action === 'create_footer') {
        $footerPage = $con->query("SELECT id FROM paginas WHERE slug = 'footer' LIMIT 1")->fetch_assoc();

        if ($footerPage) {
            $notice = 'Footer bestaat al.';
        } else {
            $siteSettings = getSiteSettings($con);
            $footerTitle = 'Footer';
            $footerSlug = 'footer';
            $footerContent = $siteSettings['footer_content'];
            $stmt = $con->prepare('INSERT INTO paginas (titel, slug, inhoud, heeft_contactformulier) VALUES (?, ?, ?, 0)');
            $stmt->bind_param('sss', $footerTitle, $footerSlug, $footerContent);
            $stmt->execute();
            $notice = 'Footer aangemaakt. Je kunt nu kolommen en afbeeldingen toevoegen.';
            $stmt->close();
        }
    }
}

$paginas = $con->query('SELECT * FROM paginas ORDER BY titel ASC');
$socials = $con->query('SELECT * FROM socials ORDER BY volgorde ASC, platform ASC');
$berichten = $con->query('SELECT * FROM contactberichten ORDER BY created_at DESC');
$footerPage = $con->query("SELECT id FROM paginas WHERE slug = 'footer' LIMIT 1")->fetch_assoc();
$footerPageId = (int) ($footerPage['id'] ?? 0);
?>

<div class="admin-panel">
    <h1>Welkom op het admin paneel</h1>
    <p>Hier kun je pagina's, social media links en contactberichten beheren.</p>

    <?php if ($notice !== ''): ?>
        <div class="pop-up <?= $noticeError ? 'pop-up--error' : 'pop-up--success' ?>">
            <p><?= testInput($notice) ?></p>
        </div>
    <?php endif; ?>

    <section id="paginas" class="admin-section">
        <h2>Pagina's</h2>

        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="save_page">
            <input type="hidden" name="id" value="0">
            <div class="inputField">
                <label for="page-titel">Titel</label>
                <input type="text" name="titel" id="page-titel" required>
            </div>
            <div class="inputField">
                <label for="page-slug">Slug (URL-vriendelijk)</label>
                <input type="text" name="slug" id="page-slug" required placeholder="bijv. over-ons">
            </div>
            <div class="inputField">
                <label for="page-inhoud">Inhoud</label>
                <textarea name="inhoud" id="page-inhoud" rows="4" required></textarea>
            </div>
            <div class="inputField inputField--checkbox">
                <label>
                    <input type="checkbox" name="heeft_contactformulier" value="1">
                    Contactformulier op deze pagina
                </label>
            </div>
            <button type="submit">Pagina toevoegen</button>
        </form>

        <?php if ($paginas && $paginas->num_rows > 0): ?>
            <div class="admin-list">
                <?php while ($pagina = $paginas->fetch_assoc()): ?>
                    <article class="admin-card">
                        <form method="post" class="admin-form">
                            <input type="hidden" name="action" value="save_page">
                            <input type="hidden" name="id" value="<?= (int) $pagina['id'] ?>">
                            <div class="inputField">
                                <label>Titel</label>
                                <input type="text" name="titel" value="<?= testInput($pagina['titel']) ?>" required>
                            </div>
                            <div class="inputField">
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?= testInput($pagina['slug']) ?>" required>
                            </div>
                            <div class="inputField">
                                <label>Inhoud</label>
                                <textarea name="inhoud" rows="4" required><?= testInput($pagina['inhoud']) ?></textarea>
                            </div>
                            <div class="inputField inputField--checkbox">
                                <label>
                                    <input type="checkbox" name="heeft_contactformulier" value="1"
                                        <?= !empty($pagina['heeft_contactformulier']) ? 'checked' : '' ?>>
                                    Contactformulier op deze pagina
                                </label>
                            </div>
                            <div class="admin-card__actions">
                                <button type="submit">Opslaan</button>
                                <a class="admin-view-link" href="<?= view('page_builder.php') ?>?page_id=<?= (int) $pagina['id'] ?>">Layout bewerken</a>
                                <a class="admin-view-link" href="<?= view('pages.php') ?>?slug=<?= urlencode($pagina['slug']) ?>" target="_blank">Bekijk pagina</a>
                            </div>
                        </form>
                        <form method="post" onsubmit="return confirm('Deze pagina verwijderen?');">
                            <input type="hidden" name="action" value="delete_page">
                            <input type="hidden" name="id" value="<?= (int) $pagina['id'] ?>">
                            <button type="submit" class="btn-danger">Verwijderen</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <p class="admin-empty">Nog geen pagina's.</p>
        <?php endif; ?>
    </section>

    <section id="socials" class="admin-section">
        <h2>Social media links</h2>

        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="save_social">
            <input type="hidden" name="id" value="0">
            <div class="inputField">
                <label for="social-platform">Platform</label>
                <input type="text" name="platform" id="social-platform" required placeholder="Instagram">
            </div>
            <div class="inputField">
                <label for="social-url">URL</label>
                <input type="url" name="url" id="social-url" required placeholder="https://">
            </div>
            <div class="inputField">
                <label for="social-volgorde">Volgorde</label>
                <input type="number" name="volgorde" id="social-volgorde" value="0">
            </div>
            <button type="submit">Link toevoegen</button>
        </form>

        <?php if ($socials && $socials->num_rows > 0): ?>
            <div class="admin-list">
                <?php while ($social = $socials->fetch_assoc()): ?>
                    <article class="admin-card">
                        <form method="post" class="admin-form admin-form--inline">
                            <input type="hidden" name="action" value="save_social">
                            <input type="hidden" name="id" value="<?= (int) $social['id'] ?>">
                            <div class="inputField">
                                <label>Platform</label>
                                <input type="text" name="platform" value="<?= testInput($social['platform']) ?>" required>
                            </div>
                            <div class="inputField">
                                <label>URL</label>
                                <input type="url" name="url" value="<?= testInput($social['url']) ?>" required>
                            </div>
                            <div class="inputField">
                                <label>Volgorde</label>
                                <input type="number" name="volgorde" value="<?= (int) $social['volgorde'] ?>">
                            </div>
                            <button type="submit">Opslaan</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Deze link verwijderen?');">
                            <input type="hidden" name="action" value="delete_social">
                            <input type="hidden" name="id" value="<?= (int) $social['id'] ?>">
                            <button type="submit" class="btn-danger">Verwijderen</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="admin-empty">Nog geen social links.</p>
        <?php endif; ?>
    </section>

    <section id="footer" class="admin-section">
        <h2>Footer</h2>
        <?php if ($footerPageId > 0): ?>
            <p>Beheer de footer als pagina met tekstkolommen en afbeeldingen.</p>
            <a class="admin-view-link" href="<?= view('page_builder.php') ?>?page_id=<?= $footerPageId ?>">Footer bewerken</a>
        <?php else: ?>
            <p>Maak eerst een footerpagina aan om kolommen en afbeeldingen toe te voegen.</p>
            <form method="post">
                <input type="hidden" name="action" value="create_footer">
                <button type="submit">Footer aanmaken</button>
            </form>
        <?php endif; ?>
    </section>

    <section id="contactberichten" class="admin-section">
        <h2>Contactberichten</h2>

        <?php if ($berichten && $berichten->num_rows > 0): ?>
            <div class="admin-list">
                <?php while ($bericht = $berichten->fetch_assoc()): ?>
                    <article class="admin-card <?= $bericht['gelezen'] ? 'admin-card--read' : 'admin-card--unread' ?>">
                        <p><strong><?= testInput($bericht['naam']) ?></strong> &lt;<?= testInput($bericht['email']) ?>&gt;</p>
                        <p class="admin-meta"><?= testInput($bericht['created_at']) ?> · <?= $bericht['gelezen'] ? 'Gelezen' : 'Nieuw' ?></p>
                        <p><?= nl2br(testInput($bericht['bericht'])) ?></p>
                        <div class="admin-card__actions">
                            <?php if (!$bericht['gelezen']): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="id" value="<?= (int) $bericht['id'] ?>">
                                    <button type="submit">Markeer als gelezen</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Dit bericht verwijderen?');">
                                <input type="hidden" name="action" value="delete_message">
                                <input type="hidden" name="id" value="<?= (int) $bericht['id'] ?>">
                                <button type="submit" class="btn-danger">Verwijderen</button>
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="admin-empty">Nog geen contactberichten.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../core/admin_footer.php'; ?>
