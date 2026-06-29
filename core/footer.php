    </main>
    <footer class="site-footer" style="background: var(--footer-bg); color: var(--footer-text);">
        <?php if (!empty($siteSettings['footer_html'])): ?>
            <?= $siteSettings['footer_html'] ?>
        <?php else: ?>
            <p>&copy; <?= date('Y') ?> ShiftsHappen</p>
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
