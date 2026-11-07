
<?php
require_once "config/koneksi.php";

// Menentukan halaman yang dibuka
$page = $_GET['page'] ?? 'dashboard';

// Daftar halaman yang diizinkan
$halaman = ['dashboard', 'donatur', 'donasi', 'rekap'];

if (!in_array($page, $halaman)) {
    $page = 'dashboard';
}

include "includes/header.php";
include "includes/sidebar.php";
?>

<main class="main-content">
    <header class="topbar">
        <h1>Sistem Pengelolaan Data Donasi</h1>
        <p>Selamat datang di aplikasi pengelolaan data donasi.</p>
    </header>

    <section class="content">
        <?php
        include "pages/" . $page . ".php";
        ?>
    </section>
</main>

<?php
include "includes/footer.php";
?>