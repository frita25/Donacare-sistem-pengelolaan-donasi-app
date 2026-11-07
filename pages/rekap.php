
<?php
// Ringkasan donasi
$stmt = $pdo->query("
    SELECT
        COUNT(*) AS jumlah_transaksi,
        COALESCE(SUM(nominal), 0) AS total_donasi,
        COALESCE(AVG(nominal), 0) AS rata_rata
    FROM donasi
");
$ringkasan = $stmt->fetch(PDO::FETCH_ASSOC);

// Rekap berdasarkan metode pembayaran
$stmt = $pdo->query("
    SELECT
        metode_pembayaran,
        COUNT(*) AS jumlah_transaksi,
        SUM(nominal) AS total_donasi
    FROM donasi
    GROUP BY metode_pembayaran
    ORDER BY total_donasi DESC
");
$rekapMetode = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Rekap berdasarkan bulan
$stmt = $pdo->query("
    SELECT
        DATE_FORMAT(tanggal_donasi, '%Y-%m') AS bulan,
        COUNT(*) AS jumlah_transaksi,
        SUM(nominal) AS total_donasi
    FROM donasi
    GROUP BY DATE_FORMAT(tanggal_donasi, '%Y-%m')
    ORDER BY bulan DESC
");
$rekapBulan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detail seluruh transaksi
$stmt = $pdo->query("
    SELECT
        donasi.*,
        donatur.nama_donatur
    FROM donasi
    JOIN donatur
        ON donasi.id_donatur = donatur.id_donatur
    ORDER BY tanggal_donasi DESC, id_donasi DESC
");
$daftarRekap = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// Nama bulan Indonesia
function namaBulan($tanggal) {
    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $waktu = strtotime($tanggal);
    return $bulan[(int)date('n', $waktu)] . ' ' . date('Y', $waktu);
}
?>

<div class="rekap-page">

    <!-- Header -->
    <div class="rekap-heading">
        <h2>Rekap Donasi</h2>
        <p>Ringkasan dan laporan seluruh data donasi yang telah tercatat.</p>
    </div>

    <!-- Ringkasan utama -->
    <div class="rekap-summary">

        <div class="rekap-card">
            <div class="rekap-card-top">
                <div class="rekap-card-icon">💰</div>
            </div>
            <p class="rekap-card-label">Total Donasi</p>
            <h3><?= formatRupiah($ringkasan['total_donasi']) ?></h3>
        </div>

        <div class="rekap-card">
            <div class="rekap-card-top">
                <div class="rekap-card-icon">📋</div>
            </div>
            <p class="rekap-card-label">Jumlah Transaksi</p>
            <h3><?= number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') ?></h3>
        </div>

        <div class="rekap-card">
            <div class="rekap-card-top">
                <div class="rekap-card-icon">📈</div>
            </div>
            <p class="rekap-card-label">Rata-rata Donasi</p>
            <h3><?= formatRupiah($ringkasan['rata_rata']) ?></h3>
        </div>

    </div>

    <!-- Metode pembayaran -->
    <div class="rekap-panel">

        <div class="rekap-panel-heading">
            <h3>Rekap Metode Pembayaran</h3>
            <span>Ringkasan berdasarkan metode</span>
        </div>

        <?php if (count($rekapMetode) > 0): ?>
            <div class="rekap-method-grid">

                <?php foreach ($rekapMetode as $data): ?>
                    <div class="rekap-method-card">
                        <p>
                            <?= htmlspecialchars($data['metode_pembayaran']) ?>
                        </p>

                        <h4>
                            <?= formatRupiah($data['total_donasi']) ?>
                        </h4>

                        <small>
                            <?= $data['jumlah_transaksi'] ?> transaksi
                        </small>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php else: ?>
            <p class="rekap-empty">Belum ada data donasi.</p>
        <?php endif; ?>

    </div>

    <!-- Rekap bulanan -->
    <div class="rekap-panel">

        <div class="rekap-panel-heading">
            <h3>Rekap Berdasarkan Bulan</h3>
            <span>Akumulasi donasi setiap bulan</span>
        </div>

        <div class="rekap-table-wrap">
            <table class="rekap-table rekap-month-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Periode</th>
                        <th>Jumlah Transaksi</th>
                        <th>Total Donasi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($rekapBulan) > 0): ?>
                        <?php foreach ($rekapBulan as $i => $data): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>

                                <td>
                                    <?= namaBulan($data['bulan'] . '-01') ?>
                                </td>

                                <td>
                                    <?= $data['jumlah_transaksi'] ?> transaksi
                                </td>

                                <td class="rekap-amount">
                                    <?= formatRupiah($data['total_donasi']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="rekap-empty">
                                Belum ada data donasi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Detail transaksi -->
    <div class="rekap-panel">

        <div class="rekap-panel-heading">
            <h3>Detail Transaksi Donasi</h3>
            <span><?= count($daftarRekap) ?> data tercatat</span>
        </div>

        <div class="rekap-table-wrap">
            <table class="rekap-table rekap-detail-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Nama Donatur</th>
                        <th>Nominal</th>
                        <th>Metode Pembayaran</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($daftarRekap) > 0): ?>
                        <?php foreach ($daftarRekap as $i => $data): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>

                                <td>
                                    <?= date('d-m-Y', strtotime($data['tanggal_donasi'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_donatur']) ?>
                                </td>

                                <td class="rekap-amount">
                                    <?= formatRupiah($data['nominal']) ?>
                                </td>

                                <td>
                                    <span class="rekap-method-badge">
                                        <?= htmlspecialchars($data['metode_pembayaran']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['keterangan'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="rekap-empty">
                                Belum ada data donasi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
