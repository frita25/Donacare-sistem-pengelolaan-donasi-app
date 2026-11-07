
<?php
$pesan = "";
$jenisPesan = "success";
$editData = null;

// Fungsi untuk membersihkan input
function bersihkan($data) {
    return trim($data ?? "");
}

// Proses tambah, ubah, dan hapus
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    try {
        if ($aksi === "simpan" || $aksi === "ubah") {
            $id = (int)($_POST["id_donatur"] ?? 0);
            $nama = bersihkan($_POST["nama_donatur"] ?? "");
            $kontak = bersihkan($_POST["kontak"] ?? "");
            $catatan = bersihkan($_POST["catatan"] ?? "");

            if ($nama === "" || $kontak === "") {
                throw new Exception("Nama dan kontak wajib diisi.");
            }

            if (!preg_match('/^08[0-9]{8,11}$/', $kontak)) {
                throw new Exception(
                    "Kontak harus berupa 10–13 digit dan diawali 08."
                );
            }

            // Memeriksa nama donatur duplikat
            $cek = $pdo->prepare(
                "SELECT COUNT(*) FROM donatur
                 WHERE LOWER(nama_donatur) = LOWER(?)
                 AND id_donatur != ?"
            );
            $cek->execute([$nama, $id]);

            if ($cek->fetchColumn() > 0) {
                throw new Exception("Nama donatur sudah terdaftar.");
            }

            if ($aksi === "simpan") {
                $stmt = $pdo->prepare(
                    "INSERT INTO donatur
                    (nama_donatur, kontak, catatan)
                    VALUES (?, ?, ?)"
                );
                $stmt->execute([$nama, $kontak, $catatan]);

                $pesan = "Data donatur berhasil ditambahkan.";
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE donatur
                     SET nama_donatur = ?, kontak = ?, catatan = ?
                     WHERE id_donatur = ?"
                );
                $stmt->execute([$nama, $kontak, $catatan, $id]);

                $pesan = "Data donatur berhasil diperbarui.";
            }
        }

        if ($aksi === "hapus") {
            $id = (int)($_POST["id_donatur"] ?? 0);

            $stmt = $pdo->prepare(
                "DELETE FROM donatur WHERE id_donatur = ?"
            );
            $stmt->execute([$id]);

            $pesan = "Data donatur berhasil dihapus.";
        }

    } catch (PDOException $e) {
        $jenisPesan = "danger";

        if ($aksi === "hapus") {
            $pesan = "Donatur tidak dapat dihapus karena masih memiliki transaksi donasi.";
        } else {
            $pesan = "Terjadi kesalahan saat menyimpan data.";
        }

    } catch (Exception $e) {
        $jenisPesan = "danger";
        $pesan = $e->getMessage();
    }
}

// Mengambil data untuk diedit
if (isset($_GET["edit"])) {
    $idEdit = (int)$_GET["edit"];

    $stmt = $pdo->prepare(
        "SELECT * FROM donatur WHERE id_donatur = ?"
    );
    $stmt->execute([$idEdit]);
    $editData = $stmt->fetch();
}

// Mengambil seluruh data donatur
$daftarDonatur = $pdo->query(
    "SELECT * FROM donatur ORDER BY id_donatur DESC"
)->fetchAll();
?>

<h2>Data Donatur</h2>
<p>Kelola data orang yang memberikan donasi.</p>

<?php if ($pesan !== ""): ?>
    <div class="alert <?= $jenisPesan ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>

<div class="panel">
    <h3><?= $editData ? "Edit Donatur" : "Tambah Donatur" ?></h3>

    <form method="POST" action="index.php?page=donatur">
        <input type="hidden" name="aksi"
               value="<?= $editData ? 'ubah' : 'simpan' ?>">

        <?php if ($editData): ?>
            <input type="hidden" name="id_donatur"
                   value="<?= (int)$editData['id_donatur'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nama Donatur</label>
            <input type="text" name="nama_donatur"
                   maxlength="100" required
                   value="<?= htmlspecialchars($editData['nama_donatur'] ?? '') ?>"
                   placeholder="Masukkan nama donatur">
        </div>

        <div class="form-group">
            <label>Nomor Kontak</label>
            <input type="text" name="kontak"
                   maxlength="13" required
                   value="<?= htmlspecialchars($editData['kontak'] ?? '') ?>"
                   placeholder="Contoh: 081234567890">
        </div>

        <div class="form-group">
            <label>Catatan (Opsional)</label>
            <textarea name="catatan"
                      placeholder="Masukkan catatan"><?= htmlspecialchars($editData['catatan'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-primary">
            <?= $editData ? "Simpan Perubahan" : "Tambah Donatur" ?>
        </button>

        <?php if ($editData): ?>
            <a href="index.php?page=donatur" class="btn-secondary">
                Batal
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <h3>Daftar Donatur</h3>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Donatur</th>
                    <th>Kontak</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($daftarDonatur) > 0): ?>
                    <?php foreach ($daftarDonatur as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($d['nama_donatur']) ?></td>
                            <td><?= htmlspecialchars($d['kontak']) ?></td>
                            <td><?= htmlspecialchars($d['catatan'] ?? '-') ?></td>
                            <td class="action-cell">
                                <a class="btn-edit"
                                   href="index.php?page=donatur&edit=<?= (int)$d['id_donatur'] ?>">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="index.php?page=donatur"
                                      onsubmit="return confirm('Yakin ingin menghapus donatur ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_donatur"
                                           value="<?= (int)$d['id_donatur'] ?>">
                                    <button type="submit" class="btn-delete">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty">
                            Belum ada data donatur.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>