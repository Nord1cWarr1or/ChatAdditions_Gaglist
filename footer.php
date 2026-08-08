<?php
$themes = get_themes();
$current_theme = current_theme();
?>
<footer class="site-footer">
    <div class="footer-controls">
        <div class="theme-dropdown">
            <button class="btn-theme" onclick="toggleThemeDropdown()" id="themeBtn">
                🎨 <?= theme_name($current_theme) ?> ▾
            </button>
            <div class="theme-menu" id="themeMenu">
                <?php foreach ($themes as $id => $meta): ?>
                    <a href="<?= theme_url(['theme' => $id]) ?>"
                       class="theme-option <?= $id === $current_theme ? 'active' : '' ?>"
                       data-theme="<?= $id ?>">
                        <?= theme_name($id) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="theme-toggle" onclick="toggleDarkMode()" title="<?= str('nav_theme_toggle') ?>">
            <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
    </div>
</footer>
<script>
function getDarkMode() { return localStorage.getItem('darkMode') === 'true'; }
function applyDarkMode(dark) {
    document.body.classList.toggle('dark', dark);
    var btn = document.querySelector('.theme-toggle');
    if (btn) {
        var sun = btn.querySelector('.icon-sun');
        var moon = btn.querySelector('.icon-moon');
        if (sun) sun.style.display = dark ? 'none' : 'block';
        if (moon) moon.style.display = dark ? 'block' : 'none';
    }
}
function toggleDarkMode() {
    var next = !getDarkMode();
    localStorage.setItem('darkMode', next);
    applyDarkMode(next);
}
function toggleThemeDropdown() {
    document.getElementById('themeMenu').classList.toggle('open');
}
function toggleLangDropdown() {
    document.getElementById('langMenu').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var themeDd = document.querySelector('.theme-dropdown');
    if (themeDd && !themeDd.contains(e.target)) {
        document.getElementById('themeMenu').classList.remove('open');
    }
    var langDd = document.querySelector('.lang-dropdown');
    if (langDd && !langDd.contains(e.target)) {
        document.getElementById('langMenu').classList.remove('open');
    }
});
applyDarkMode(getDarkMode());
</script>
