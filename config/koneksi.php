<?php
$host = "localhost";
$db   = "db_donasi";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Menampilkan nominal rupiah apa adanya: tanpa desimal bila bulat,
// dua desimal bila nilainya memiliki sen (mencegah pembulatan menyesatkan).
function tampilNominal($angka) {
    $angka = (float)$angka;
    $desimal = (abs($angka - round($angka)) < 0.005) ? 0 : 2;
    return 'Rp ' . number_format($angka, $desimal, ',', '.');
}
?>
