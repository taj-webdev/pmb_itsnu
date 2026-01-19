<?php
/**
 * ============================================================
 * File: config/database.php
 * Sistem: PMB ITS NU Kalimantan
 * Deskripsi: Koneksi database menggunakan MySQLi (PHP 8.2)
 * ============================================================
 */

$host     = 'localhost';     // Host server
$user     = 'root';          // Username default XAMPP
$password = '';              // Kosongkan jika pakai default
$dbname   = 'pmb_itsnu';     // Nama database

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("❌ Koneksi database gagal: " . $conn->connect_error);
}

// Set UTF-8 untuk mencegah error karakter
$conn->set_charset("utf8mb4");

/**
 * Fungsi bantu untuk menjalankan query dengan aman
 */
function query($sql)
{
    global $conn;
    $result = $conn->query($sql);
    if (!$result) {
        die("⚠️ Query gagal: " . $conn->error);
    }
    return $result;
}

/**
 * Fungsi untuk escape input user (anti SQL Injection)
 */
function escape($data)
{
    global $conn;
    return htmlspecialchars($conn->real_escape_string(trim($data)));
}
?>
