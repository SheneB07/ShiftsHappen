<?php
require_once __DIR__ . '/../core/admin_header.php';
require_once __DIR__ . '/../core/page_grid.php';
require_once __DIR__ . '/../core/page_theme.php';
require_once __DIR__ . '/../core/site_settings.php';
require_once __DIR__ . '/../core/builder_ui.php';

$pageId = (int) ($_GET['page_id'] ?? 0);
$page = getPageForBuilder($con, $pageId);

if (!$page) {
    echo '<div class="admin-panel"><p class="admin-empty">Pagina niet gevonden.</p>';
    echo '<p><a href="' . view('admin.php') . '#paginas">Terug naar pagina\'s</a></p></div>';
    require_once __DIR__ . '/../core/admin_footer.php';
    exit();
}

$gridRows = normalizeGridRowsForBuilder(getPageGridRows($con, $pageId));
$pageTheme = getPageTheme($page, $con);
$assetBase = url('assets/');
$migrationCheck = $con->query("SHOW COLUMNS FROM paginagrid LIKE 'row_width_pct'");
$layoutMigrationOk = $migrationCheck && $migrationCheck->num_rows > 0;

$builderConfig = [
    'pageId' => $pageId,
    'apiUrl' => view('page_builder_api.php'),
    'assetBase' => $assetBase,
    'pageTitle' => $page['titel'],
    'pageSlug' => $page['slug'],
    'theme' => $pageTheme,
    'rows' => $gridRows,
];
?>

<div class="admin-panel admin-panel--wide builder-app">
    <div class="builder-header">
        <div>
            
            <p class="admin-meta">Sleep kolomsecties om te ordenen. Wijzigingen worden live getoond. Sla alles in één keer op.</p>
        </div>
        <div class="builder-header__actions">
            <a class="admin-view-link" href="<?= view('pages.php') ?>?slug=<?= urlencode($page['slug']) ?>" target="_blank">Bekijk pagina</a>
            <a class="admin-view-link" href="<?= view('admin.php') ?>#paginas">Terug</a>
        </div>
    </div>

    <div id="builder-toast" class="builder-toast" hidden></div>

    <?php if (!$layoutMigrationOk): ?>
        <div class="pop-up pop-up--error">
            <p>Layout-opties ontbreken in de database. Importeer <code>database/migration_layout_options.sql</code> in phpMyAdmin en herlaad deze pagina.</p>
        </div>
    <?php endif; ?>

    <div class="builder-layout">
        <div class="builder-editor">
            <section class="admin-form builder-theme-panel">
                <h2>Pagina kleuren</h2>
                <p class="admin-meta">Deze kleuren gelden alleen voor deze pagina. De header blijft globaal.</p>
                <div class="color-grid">
                    <div class="inputField">
                        <label for="theme-body-bg">Achtergrond (body)</label>
                        <input type="color" id="theme-body-bg" data-theme="body_bg" value="<?= testInput($pageTheme['body_bg']) ?>">
                    </div>
                    <div class="inputField">
                        <label for="theme-page-bg">Content blok</label>
                        <input type="color" id="theme-page-bg" data-theme="page_bg" value="<?= testInput($pageTheme['page_bg']) ?>">
                    </div>
                    <div class="inputField">
                        <label for="theme-page-text">Titels / tekst</label>
                        <input type="color" id="theme-page-text" data-theme="page_text_color" value="<?= testInput($pageTheme['page_text_color']) ?>">
                    </div>
                    <div class="inputField">
                        <label for="theme-footer-bg">Footer achtergrond</label>
                        <input type="color" id="theme-footer-bg" data-theme="footer_bg" value="<?= testInput($pageTheme['footer_bg']) ?>">
                    </div>
                    <div class="inputField">
                        <label for="theme-footer-text">Footer tekst</label>
                        <input type="color" id="theme-footer-text" data-theme="footer_text" value="<?= testInput($pageTheme['footer_text']) ?>">
                    </div>
                </div>
            </section>

            <section class="builder-rows-section">
                <h2>Kolommen</h2>
                <div id="builder-rows" class="builder-rows-list">
                    <?= renderBuilderRows($gridRows, $assetBase) ?>
                </div>

                <div class="admin-form admin-form--inline builder-add-row">
                    <div class="inputField">
                        <label for="new-column-type">Layout (kolommen naast elkaar)</label>
                        <select id="new-column-type">
                            <option value="1">1 kolom</option>
                            <option value="2">2 kolommen</option>
                            <option value="3">2 kolommen (variant)</option>
                            <option value="4">3 kolommen</option>
                        </select>
                    </div>
                    <button type="button" id="add-row-btn">Kolomsectie toevoegen</button>
                </div>
            </section>
        </div>

        <aside class="builder-preview-panel">
            <h2>Live preview</h2>
            <div id="builder-preview" class="builder-preview-canvas">
                <div class="preview-body">
                    <div class="preview-page">
                        <h3 class="preview-title"><?= testInput($page['titel']) ?></h3>
                        <div id="preview-content"></div>
                    </div>
                    <div class="preview-footer">Footer preview</div>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="builder-save-bar">
    <span id="save-status" class="save-status">Geen wijzigingen</span>
    <button type="button" id="save-page-btn" class="btn-save-page">Pagina opslaan</button>
</div>

<script>
    window.BUILDER_CONFIG = <?= json_encode($builderConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= asset('js/grid-styles.js') ?>?v=3"></script>
<script src="<?= asset('js/admin-builder.js') ?>?v=3"></script>

<?php require_once __DIR__ . '/../core/admin_footer.php'; ?>
