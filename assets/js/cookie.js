(function () {
    var banner = document.getElementById('cookie-banner');
    if (!banner) {
        return;
    }

    var storageKey = 'shiftshappen_cookie_accepted';
    if (localStorage.getItem(storageKey) === '1') {
        banner.remove();
        return;
    }

    banner.hidden = false;

    var button = banner.querySelector('[data-cookie-accept]');
    if (button) {
        button.addEventListener('click', function () {
            localStorage.setItem(storageKey, '1');
            banner.remove();
        });
    }
})();
