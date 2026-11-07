
<?php
// Mengambil ringkasan data dari database
$totalDonatur = $pdo->query(
    "SELECT COUNT(*) FROM donatur"
)->fetchColumn();

$totalTransaksi = $pdo->query(
    "SELECT COUNT(*) FROM donasi"
)->fetchColumn();

$totalDana = $pdo->query(
    "SELECT COALESCE(SUM(nominal), 0) FROM donasi"
)->fetchColumn();

$rataDonasi = $pdo->query(
    "SELECT COALESCE(AVG(nominal), 0) FROM donasi"
)->fetchColumn();

// Mengambil ringkasan donasi berdasarkan metode pembayaran
$metodeDonasi = $pdo->query(
    "SELECT metode_pembayaran, SUM(nominal) AS total
     FROM donasi
     GROUP BY metode_pembayaran
     ORDER BY total DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Mengambil 5 transaksi terbaru
$transaksiTerbaru = $pdo->query(
    "SELECT d.*, dn.nama_donatur
     FROM donasi d
     JOIN donatur dn ON d.id_donatur = dn.id_donatur
     ORDER BY d.tanggal_donasi DESC, d.id_donasi DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// Menentukan nilai terbesar untuk persentase grafik
$nilaiTerbesar = 0;
foreach ($metodeDonasi as $metode) {
    if ($metode['total'] > $nilaiTerbesar) {
        $nilaiTerbesar = $metode['total'];
    }
}
?>

<div class="dashboard-page">

    <!-- Bagian Sambutan -->
    <section class="dashboard-welcome">
        <div>
            <span class="welcome-label">DONACARE DASHBOARD</span>
            <h2>Selamat Datang di DonaCare! 👋</h2>
            <p>
                Pantau dan kelola seluruh aktivitas donasi
                dengan lebih mudah melalui satu tempat.
            </p>
        </div>

        <div class="welcome-icon">
            <span>💚</span>
        </div>
    </section>

    <!-- Statistik Utama -->
    <section class="dashboard-statistics">

        <div class="stat-card stat-donatur">
            <div class="stat-card-top">
                <span>Total Donatur</span>
                <div class="stat-icon">👥</div>
            </div>
            <h3><?= number_format($totalDonatur) ?></h3>
            <small>Jumlah donatur terdaftar</small>
        </div>

        <div class="stat-card stat-transaksi">
            <div class="stat-card-top">
                <span>Total Transaksi</span>
                <div class="stat-icon">🧾</div>
            </div>
            <h3><?= number_format($totalTransaksi) ?></h3>
            <small>Transaksi donasi tercatat</small>
        </div>

        <div class="stat-card stat-dana">
            <div class="stat-card-top">
                <span>Total Dana Terkumpul</span>
                <div class="stat-icon">💰</div>
            </div>
            <h3>Rp <?= number_format($totalDana, 0, ',', '.') ?></h3>
            <small>Total seluruh nominal donasi</small>
        </div>

        <div class="stat-card stat-rata">
            <div class="stat-card-top">
                <span>Rata-rata Donasi</span>
                <div class="stat-icon">📊</div>
            </div>
            <h3>Rp <?= number_format($rataDonasi, 0, ',', '.') ?></h3>
            <small>Rata-rata setiap transaksi</small>
        </div>

    </section>

    <!-- Ringkasan dan Akses Cepat -->
    <section class="dashboard-content-grid">

        <div class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h3>Ringkasan Metode Pembayaran</h3>
                    <p>Total donasi berdasarkan metode pembayaran</p>
                </div>
                <span class="panel-heading-icon">📈</span>
            </div>

            <?php if (count($metodeDonasi) > 0): ?>
                <div class="payment-summary">
                    <?php foreach ($metodeDonasi as $metode): ?>
                        <?php
                        $persentase = $nilaiTerbesar > 0
                            ? ($metode['total'] / $nilaiTerbesar) * 100
                            : 0;
                        ?>
                        <div class="payment-item">
                            <div class="payment-info">
                                <span>
                                    <?= htmlspecialchars($metode['metode_pembayaran']) ?>
                                </span>
                                <strong>
                                    Rp <?= number_format($metode['total'], 0, ',', '.') ?>
                                </strong>
                            </div>
                            <div class="payment-progress">
                                <div class="payment-progress-fill"
                                     style="width: <?= $persentase ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">
                    <span>📊</span>
                    <p>Belum ada data donasi untuk ditampilkan.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-panel quick-action-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h3>Akses Cepat</h3>
                    <p>Pilih aktivitas yang ingin dilakukan</p>
                </div>
                <span class="panel-heading-icon">⚡</span>
            </div>

            <a href="index.php?page=donatur" class="quick-action-link">
                <span class="quick-action-icon">👤</span>
                <span>
                    <strong>Kelola Donatur</strong>
                    <small>Tambah dan perbarui data donatur</small>
                </span>
                <span class="quick-arrow">›</span>
            </a>

            <a href="index.php?page=donasi" class="quick-action-link">
                <span class="quick-action-icon">💚</span>
                <span>
                    <strong>Catat Donasi</strong>
                    <small>Masukkan transaksi donasi baru</small>
                </span>
                <span class="quick-arrow">›</span>
            </a>

            <a href="index.php?page=rekap" class="quick-action-link">
                <span class="quick-action-icon">📋</span>
                <span>
                    <strong>Lihat Rekapitulasi</strong>
                    <small>Lihat ringkasan seluruh donasi</small>
                </span>
                <span class="quick-arrow">›</span>
            </a>
        </div>

    </section>

    <!-- Transaksi Terbaru -->
    <section class="dashboard-panel dashboard-recent-panel">
        <div class="dashboard-panel-heading">
            <div>
                <h3>Transaksi Donasi Terbaru</h3>
                <p>Lima transaksi terakhir yang tercatat dalam sistem</p>
            </div>
            <a href="index.php?page=donasi" class="view-all-link">
                Lihat Semua →
            </a>
        </div>

        <div class="dashboard-table-wrapper">
            <table class="dashboard-recent-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Nama Donatur</th>
                        <th>Metode Pembayaran</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transaksiTerbaru) > 0): ?>
                        <?php foreach ($transaksiTerbaru as $index => $transaksi): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <?= date('d-m-Y', strtotime($transaksi['tanggal_donasi'])) ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($transaksi['nama_donatur']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="dashboard-method-badge">
                                        <?= htmlspecialchars($transaksi['metode_pembayaran']) ?>
                                    </span>
                                </td>
                                <td class="dashboard-nominal">
                                    Rp <?= number_format($transaksi['nominal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="dashboard-no-data">
                                Belum ada transaksi donasi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>
