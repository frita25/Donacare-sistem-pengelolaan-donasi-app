
<aside class="sidebar">
    <div class="brand">
        <h2>DonaCare</h2>
        <p>Sistem Pengelolaan Data Donasi</p>
    </div>

    <nav class="menu">
        <a href="index.php?page=dashboard"
           class="<?= $page == 'dashboard' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="index.php?page=donatur"
           class="<?= $page == 'donatur' ? 'active' : '' ?>">
            Data Donatur
        </a>

        <a href="index.php?page=donasi"
           class="<?= $page == 'donasi' ? 'active' : '' ?>">
            Data Donasi
        </a>

        <a href="index.php?page=rekap"
           class="<?= $page == 'rekap' ? 'active' : '' ?>">
            Rekap Donasi
        </a>
    </nav>
</aside>