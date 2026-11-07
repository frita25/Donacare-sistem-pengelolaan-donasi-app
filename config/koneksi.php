<?php
try {
    $db_file = __DIR__ . '/database.sqlite';
    $pdo = new PDO("sqlite:" . $db_file);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS donasi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama TEXT NOT NULL,
        donatur TEXT,
        nominal REAL NOT NULL,
        tanggal TEXT NOT NULL
    )");
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
