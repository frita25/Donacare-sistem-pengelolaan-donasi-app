<?php
// =====================================
// PROSES TAMBAH, EDIT, DAN HAPUS DONASI
// =====================================

$pesan = '';
$jenisPesan = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // TAMBAH DONASI
    if ($aksi === 'tambah' || $aksi === 'update') {
        $idDonasi = (int) ($_POST['id_donasi'] ?? 0);
        $idDonatur = (int) ($_POST['id_donatur'] ?? 0);
        $tanggal = trim($_POST['tanggal_donasi'] ?? '');
        $nominalInput = trim($_POST['nominal'] ?? '');
        $metode = trim($_POST['metode_pembayaran'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        $daftarMetode = ['Tunai', 'Transfer', 'E-Wallet'];

        // Validasi input
        if ($idDonatur <= 0 || $tanggal === '' || $nominalInput === '' || $metode === '') {
            $pesan = 'Semua kolom wajib harus diisi.';
            $jenisPesan = 'error';
        } elseif (!in_array($metode, $daftarMetode, true)) {
            $pesan = 'Metode pembayaran tidak valid.';
            $jenisPesan = 'error';
        } elseif (!is_numeric($nominalInput) || (float)$nominalInput <= 0 || (float)$nominalInput > 9999999999.99) {
            $pesan = 'Nominal donasi harus berupa angka lebih dari 0 dan tidak melebihi batas.';
            $jenisPesan = 'error';
        } else {
            $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);
            $tanggalValid = $tanggalObj && $tanggalObj->format('Y-m-d') === $tanggal;

            if (!$tanggalValid) {
                $pesan = 'Format tanggal donasi tidak valid.';
                $jenisPesan = 'error';
            } elseif ($tanggal > date('Y-m-d')) {
                $pesan = 'Tanggal donasi tidak boleh melebihi hari ini.';
                $jenisPesan = 'error';
            } else {
                // Pastikan donatur tersedia
                $cekDonatur = $pdo->prepare(
                    "SELECT COUNT(*) FROM donatur WHERE id_donatur = ?"
                );
                $cekDonatur->execute([$idDonatur]);

                if ((int)$cekDonatur->fetchColumn() === 0) {
                    $pesan = 'Donatur yang dipilih tidak ditemukan.';
                    $jenisPesan = 'error';
                } else {
                    try {
                        if ($aksi === 'tambah') {
                            $stmt = $pdo->prepare(
                                "INSERT INTO donasi
                                (id_donatur, tanggal_donasi, nominal, metode_pembayaran, keterangan)
                                VALUES (?, ?, ?, ?, ?)"
                            );

                            $stmt->execute([
                                $idDonatur,
                                $tanggal,
                                $nominalInput,
                                $metode,
                                $keterangan !== '' ? $keterangan : null
                            ]);

                            $pesan = 'Data donasi berhasil ditambahkan.';
                        } else {
                            $stmt = $pdo->prepare(
                                "UPDATE donasi
                                SET id_donatur = ?, tanggal_donasi = ?, nominal = ?,
                                    metode_pembayaran = ?, keterangan = ?
                                WHERE id_donasi = ?"
                            );

                            $stmt->execute([
                                $idDonatur,
                                $tanggal,
                                $nominalInput,
                                $metode,
                                $keterangan !== '' ? $keterangan : null,
                                $idDonasi
                            ]);

                            $pesan = 'Data donasi berhasil diperbarui.';
                        }
                    } catch (PDOException $e) {
                        $pesan = 'Data donasi gagal disimpan. Silakan periksa kembali.';
                        $jenisPesan = 'error';
                    }
                }
            }
        }
    }

    // HAPUS DONASI
    if ($aksi === 'hapus') {
        $idDonasi = (int) ($_POST['id_donasi'] ?? 0);

        if ($idDonasi > 0) {
            try {
                $stmt = $pdo->prepare(
                    "DELETE FROM donasi WHERE id_donasi = ?"
                );
                $stmt->execute([$idDonasi]);

                $pesan = 'Data donasi berhasil dihapus.';
            } catch (PDOException $e) {
                $pesan = 'Data donasi gagal dihapus.';
                $jenisPesan = 'error';
            }
        }
    }
}

// =====================================
// AMBIL DATA UNTUK FORM DAN EDIT
// =====================================

$daftarDonatur = $pdo->query(
    "SELECT id_donatur, nama_donatur FROM donatur ORDER BY nama_donatur ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$editData = null;

if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM donasi WHERE id_donasi = ?"
    );
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =====================================
// PENCARIAN DAN FILTER DONASI
// =====================================

$cari = trim($_GET['cari'] ?? '');
$filterMetode = trim($_GET['metode'] ?? '');
$tanggalAwal = trim($_GET['tanggal_awal'] ?? '');
$tanggalAkhir = trim($_GET['tanggal_akhir'] ?? '');

$sql = "SELECT donasi.*, donatur.nama_donatur
        FROM donasi
        JOIN donatur ON donasi.id_donatur = donatur.id_donatur
        WHERE 1=1";

$params = [];

if ($cari !== '') {
    $sql .= " AND donatur.nama_donatur LIKE ?";
    $params[] = '%' . $cari . '%';
}

if (in_array($filterMetode, ['Tunai', 'Transfer', 'E-Wallet'], true)) {
    $sql .= " AND donasi.metode_pembayaran = ?";
    $params[] = $filterMetode;
}

if ($tanggalAwal !== '') {
    $sql .= " AND donasi.tanggal_donasi >= ?";
    $params[] = $tanggalAwal;
}

if ($tanggalAkhir !== '') {
    $sql .= " AND donasi.tanggal_donasi <= ?";
    $params[] = $tanggalAkhir;
}

$sql .= " ORDER BY donasi.tanggal_donasi DESC, donasi.id_donasi DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarDonasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper untuk menampilkan teks dengan aman
function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Parameter filter sedang aktif atau tidak
$filterAktif = (
    $filterMetode !== '' ||
    $tanggalAwal !== '' ||
    $tanggalAkhir !== ''
);
?>

<div class="donasi-page">

    <div class="donasi-heading">
        <div>
            <h2>Data Donasi</h2>
            <p>Kelola data transaksi donasi yang telah diterima.</p>
        </div>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="donasi-alert <?= $jenisPesan === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT DONASI -->
    <div class="donasi-panel">
        <h3><?= $editData ? 'Edit Data Donasi' : 'Tambah Donasi' ?></h3>

        <?php if (count($daftarDonatur) === 0): ?>
            <p class="donasi-empty">
                Belum ada data donatur. Tambahkan data donatur terlebih dahulu.
            </p>
        <?php else: ?>

            <form method="POST" action="index.php?page=donasi" class="donasi-form">

                <input
                    type="hidden"
                    name="aksi"
                    value="<?= $editData ? 'update' : 'tambah' ?>"
                >

                <?php if ($editData): ?>
                    <input
                        type="hidden"
                        name="id_donasi"
                        value="<?= aman($editData['id_donasi']) ?>"
                    >
                <?php endif; ?>

                <div class="donasi-form-grid">

                    <div class="donasi-field">
                        <label>Nama Donatur <span>*</span></label>
                        <select name="id_donatur" required>
                            <option value="">-- Pilih Donatur --</option>

                            <?php foreach ($daftarDonatur as $donatur): ?>
                                <option
                                    value="<?= aman($donatur['id_donatur']) ?>"
                                    <?= ($editData && $editData['id_donatur'] == $donatur['id_donatur']) ? 'selected' : '' ?>
                                >
                                    <?= aman($donatur['nama_donatur']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="donasi-field">
                        <label>Tanggal Donasi <span>*</span></label>
                        <input
                            type="date"
                            name="tanggal_donasi"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= aman($editData['tanggal_donasi'] ?? date('Y-m-d')) ?>"
                            required
                        >
                    </div>

                    <div class="donasi-field">
                        <label>Nominal Donasi <span>*</span></label>
                        <input
                            type="number"
                            name="nominal"
                            min="1"
                            step="0.01"
                            placeholder="Contoh: 100000"
                            value="<?= aman($editData['nominal'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="donasi-field">
                        <label>Metode Pembayaran <span>*</span></label>
                        <select name="metode_pembayaran" required>
                            <option value="">-- Pilih Metode --</option>

                            <?php
                            $metodeTerpilih = $editData['metode_pembayaran'] ?? '';
                            $pilihanMetode = ['Tunai', 'Transfer', 'E-Wallet'];
                            ?>

                            <?php foreach ($pilihanMetode as $metode): ?>
                                <option
                                    value="<?= aman($metode) ?>"
                                    <?= $metodeTerpilih === $metode ? 'selected' : '' ?>
                                >
                                    <?= aman($metode) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="donasi-field donasi-field-full">
                        <label>Keterangan</label>
                        <textarea
                            name="keterangan"
                            rows="3"
                            placeholder="Keterangan tambahan (opsional)"
                        ><?= aman($editData['keterangan'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="donasi-form-actions">
                    <button type="submit" class="donasi-btn donasi-btn-primary">
                        <?= $editData ? 'Simpan Perubahan' : 'Tambah Donasi' ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="index.php?page=donasi" class="donasi-btn donasi-btn-secondary">
                            Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- TABEL DATA DONASI -->
    <div class="donasi-panel">

        <div class="donasi-table-heading">
            <div>
                <h3>Daftar Transaksi Donasi</h3>
                <p><?= count($daftarDonasi) ?> data ditampilkan</p>
            </div>

            <button
                type="button"
                class="donasi-btn donasi-btn-filter"
                onclick="bukaFilter()"
            >
                <span>☷</span> Filter
                <?php if ($filterAktif): ?>
                    <span class="filter-indicator"></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- PENCARIAN NAMA DONATUR -->
        <form method="GET" action="index.php" class="donasi-search-form">
            <input type="hidden" name="page" value="donasi">
            <input type="hidden" name="metode" value="<?= aman($filterMetode) ?>">
            <input type="hidden" name="tanggal_awal" value="<?= aman($tanggalAwal) ?>">
            <input type="hidden" name="tanggal_akhir" value="<?= aman($tanggalAkhir) ?>">

            <div class="donasi-search-box">
                <span>⌕</span>
                <input
                    type="text"
                    name="cari"
                    placeholder="Cari nama donatur..."
                    value="<?= aman($cari) ?>"
                >
            </div>

            <button type="submit" class="donasi-btn donasi-btn-primary">
                Cari
            </button>

            <?php if ($cari !== '' || $filterAktif): ?>
                <a href="index.php?page=donasi" class="donasi-reset-link">
                    Reset
                </a>
            <?php endif; ?>
        </form>

        <?php if ($filterAktif): ?>
            <div class="donasi-filter-info">
                Filter sedang diterapkan.
                <?php if ($filterMetode !== ''): ?>
                    Metode: <strong><?= aman($filterMetode) ?></strong>.
                <?php endif; ?>
                <?php if ($tanggalAwal !== ''): ?>
                    Mulai: <strong><?= aman($tanggalAwal) ?></strong>.
                <?php endif; ?>
                <?php if ($tanggalAkhir !== ''): ?>
                    Sampai: <strong><?= aman($tanggalAkhir) ?></strong>.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="donasi-table-wrapper">
            <table class="donasi-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Donatur</th>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($daftarDonasi) > 0): ?>
                        <?php $no = 1; ?>

                        <?php foreach ($daftarDonasi as $donasi): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= aman($donasi['nama_donatur']) ?></td>
                                <td>
                                    <?= date('d-m-Y', strtotime($donasi['tanggal_donasi'])) ?>
                                </td>
                                <td>
                                    <?= tampilNominal($donasi['nominal']) ?>
                                </td>
                                <td>
                                    <span class="donasi-method">
                                        <?= aman($donasi['metode_pembayaran']) ?>
                                    </span>
                                </td>
                                <td><?= aman($donasi['keterangan'] ?: '-') ?></td>
                                <td>
                                    <div class="donasi-action-group">
                                        <a
                                            href="index.php?page=donasi&edit=<?= aman($donasi['id_donasi']) ?>"
                                            class="donasi-action-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="index.php?page=donasi"
                                            onsubmit="return confirm('Yakin ingin menghapus data donasi ini?')"
                                        >
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input
                                                type="hidden"
                                                name="id_donasi"
                                                value="<?= aman($donasi['id_donasi']) ?>"
                                            >
                                            <button type="submit" class="donasi-action-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="donasi-no-data">
                                Data donasi tidak ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- =====================================
     MODAL WIZARD FILTER DONASI
===================================== -->

<div class="donasi-modal-overlay" id="modalFilter">
    <div class="donasi-modal">

        <div class="donasi-modal-header">
            <div>
                <h3>Filter Data Donasi</h3>
                <p>Atur kriteria untuk menampilkan data donasi.</p>
            </div>

            <button
                type="button"
                class="donasi-modal-close"
                onclick="tutupFilter()"
            >
                &times;
            </button>
        </div>

        <div class="donasi-wizard-progress">
            <div class="donasi-progress-step active" id="progress1"></div>
            <div class="donasi-progress-step" id="progress2"></div>
        </div>

        <div class="donasi-wizard-label" id="labelLangkah">
            Langkah 1 dari 2
        </div>

        <form method="GET" action="index.php" id="formFilterWizard">
            <input type="hidden" name="page" value="donasi">
            <input type="hidden" name="cari" value="<?= aman($cari) ?>">

            <!-- LANGKAH 1 -->
            <div class="donasi-wizard-content active" id="langkah1">
                <h4>Pilih Metode Pembayaran</h4>
                <p class="donasi-wizard-description">
                    Pilih metode pembayaran yang ingin ditampilkan.
                </p>

                <div class="donasi-field">
                    <label>Metode Pembayaran</label>
                    <select name="metode" id="filterMetode">
                        <option value="">Semua Metode</option>
                        <option value="Tunai" <?= $filterMetode === 'Tunai' ? 'selected' : '' ?>>
                            Tunai
                        </option>
                        <option value="Transfer" <?= $filterMetode === 'Transfer' ? 'selected' : '' ?>>
                            Transfer
                        </option>
                        <option value="E-Wallet" <?= $filterMetode === 'E-Wallet' ? 'selected' : '' ?>>
                            E-Wallet
                        </option>
                    </select>
                </div>
            </div>

            <!-- LANGKAH 2 -->
            <div class="donasi-wizard-content" id="langkah2">
                <h4>Tentukan Rentang Tanggal</h4>
                <p class="donasi-wizard-description">
                    Tentukan periode donasi yang ingin ditampilkan.
                </p>

                <div class="donasi-field">
                    <label>Tanggal Mulai</label>
                    <input
                        type="date"
                        name="tanggal_awal"
                        id="tanggalAwal"
                        value="<?= aman($tanggalAwal) ?>"
                        max="<?= date('Y-m-d') ?>"
                    >
                </div>

                <div class="donasi-field">
                    <label>Tanggal Akhir</label>
                    <input
                        type="date"
                        name="tanggal_akhir"
                        id="tanggalAkhir"
                        value="<?= aman($tanggalAkhir) ?>"
                        max="<?= date('Y-m-d') ?>"
                    >
                </div>

                <p class="donasi-wizard-hint">
                    Kosongkan tanggal jika tidak ingin menggunakan filter periode.
                </p>
            </div>

            <div class="donasi-wizard-actions">
                <button
                    type="button"
                    class="donasi-btn donasi-btn-secondary"
                    id="tombolKembali"
                    onclick="langkahSebelumnya()"
                    style="display:none;"
                >
                    Kembali
                </button>

                <button
                    type="button"
                    class="donasi-btn donasi-btn-primary"
                    id="tombolLanjut"
                    onclick="langkahSelanjutnya()"
                >
                    Selanjutnya
                </button>

                <button
                    type="submit"
                    class="donasi-btn donasi-btn-primary"
                    id="tombolTerapkan"
                    style="display:none;"
                >
                    Terapkan Filter
                </button>
            </div>
        </form>

        <div class="donasi-modal-footer">
            <a href="index.php?page=donasi" class="donasi-reset-link">
                Reset semua filter
            </a>
        </div>

    </div>
</div>


<script>
let langkahAktif = 1;

function bukaFilter() {
    document.getElementById('modalFilter').classList.add('show');
    document.body.classList.add('modal-open');
    tampilkanLangkah(1);
}

function tutupFilter() {
    document.getElementById('modalFilter').classList.remove('show');
    document.body.classList.remove('modal-open');
}

function tampilkanLangkah(nomor) {
    langkahAktif = nomor;

    document.getElementById('langkah1').classList.toggle('active', nomor === 1);
    document.getElementById('langkah2').classList.toggle('active', nomor === 2);

    document.getElementById('progress1').classList.toggle('active', nomor >= 1);
    document.getElementById('progress2').classList.toggle('active', nomor >= 2);

    document.getElementById('labelLangkah').textContent =
        'Langkah ' + nomor + ' dari 2';

    document.getElementById('tombolKembali').style.display =
        nomor === 1 ? 'none' : 'inline-flex';

    document.getElementById('tombolLanjut').style.display =
        nomor === 1 ? 'inline-flex' : 'none';

    document.getElementById('tombolTerapkan').style.display =
        nomor === 2 ? 'inline-flex' : 'none';
}

function langkahSelanjutnya() {
    tampilkanLangkah(2);
}

function langkahSebelumnya() {
    tampilkanLangkah(1);
}

// Tutup modal jika area luar pop-up diklik
document.getElementById('modalFilter').addEventListener('click', function(event) {
    if (event.target === this) {
        tutupFilter();
    }
});

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tutupFilter();
    }
});

// Validasi tanggal filter
document.getElementById('formFilterWizard').addEventListener('submit', function(event) {
    const awal = document.getElementById('tanggalAwal').value;
    const akhir = document.getElementById('tanggalAkhir').value;

    if (awal && akhir && awal > akhir) {
        event.preventDefault();
        alert('Tanggal mulai tidak boleh melebihi tanggal akhir.');
    }
});
</script>
