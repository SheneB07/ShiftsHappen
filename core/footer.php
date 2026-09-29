    </main>
    <?php
    $siteSettings = getSiteSettings($con);
    ?>
    <?php
    require_once __DIR__ . '/page_grid.php';
    $footerPageResult = $con->query("SELECT id, inhoud FROM paginas WHERE slug = 'footer' LIMIT 1");
    $footerPage = $footerPageResult ? $footerPageResult->fetch_assoc() : null;
    $footerRows = $footerPage ? getPageGridRows($con, (int) $footerPage['id']) : [];
    ?>
    <footer class="site-footer">
        <?php if (!empty($footerRows)): ?>
            <div class="site-footer__content">
                <?php foreach ($footerRows as $gridRow): ?>
                    <?php
                    $columnAmount = gridColumnCount((int) $gridRow['columnType']);
                    $rowLayout = normalizeRowLayout($gridRow);
                    $flushRow = !empty($rowLayout['flush_columns']) || (int) $rowLayout['column_gap'] === 0;
                    ?>
                    <div class="rowContainer">
                        <div class="row-wrapper" style="<?= buildRowWrapperStyle($gridRow) ?>">
                            <div class="<?= rowGridClass((int) $gridRow['columnType'], $gridRow) ?>" style="<?= buildRowGridStyle($gridRow, $gridRow['columns'], $columnAmount) ?>">
                                <?php for ($columnId = 1; $columnId <= $columnAmount; $columnId++): ?>
                                    <?php $column = $gridRow['columns'][$columnId] ?? null; ?>
                                    <?php if (!$column) continue; ?>
                                    <?php
                                    $isImage = (int) ($column['foto'] ?? 0) === 1;
                                    $opacity = max(0, min(1, ((int) ($column['opacity'] ?? 10)) / 10));
                                    $columnStyle = buildColumnStyle($column, $isImage, $flushRow);
                                    ?>
                                    <div class="grid-cell" style="<?= $columnStyle ?>">
                                        <?php if ($isImage && !empty($column['informatie'])): ?>
                                            <div class="grid-image<?= $flushRow ? ' grid-image--flush' : '' ?>">
                                                <img class="team-image" src="<?= asset('img/fotos/' . rawurlencode($column['informatie'])) ?>" style="opacity: <?= $opacity ?>;" alt="Afbeelding">
                                            </div>
                                        <?php elseif (!$isImage): ?>
                                            <p class="home-text<?= !empty($column['italic']) ? ' italic' : '' ?><?= !empty($column['bold']) ? ' bold' : '' ?>" style="opacity: <?= $opacity ?>; color: <?= testInput($column['kleur'] ?? '#f9fafb') ?>;">
                                                <?= nl2br(testInput($column['informatie'] ?? '')) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <h2><?= testInput($siteSettings['footer_title']) ?></h2>
            <p><?= nl2br(testInput($footerPage['inhoud'] ?? $siteSettings['footer_content'])) ?></p>
        <?php endif; ?>
        <p><a href="<?= view('login.php') ?>">Admin login</a></p>
    </footer>

    <?php if (!empty($siteSettings['cookie_enabled'])): ?>
        <div id="cookie-banner" class="cookie-banner" hidden
            style="background: <?= testInput($siteSettings['cookie_bg']) ?>; color: <?= testInput($siteSettings['cookie_text_color']) ?>;">
            <p class="cookie-banner__text"><?= testInput($siteSettings['cookie_tekst']) ?></p>
            <button type="button" data-cookie-accept
                style="background: <?= testInput($siteSettings['cookie_button_bg']) ?>; color: <?= testInput($siteSettings['cookie_button_text_color']) ?>;">
                <?= testInput($siteSettings['cookie_button_text']) ?>
            </button>
        </div>
        <script src="<?= asset('js/cookie.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
