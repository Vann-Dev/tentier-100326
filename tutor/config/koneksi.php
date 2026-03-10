<?php
// =====================================================
// File: config/koneksi.php
// Deskripsi: Konfigurasi dan koneksi database MySQL
// =====================================================

// Konfigurasi server database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // user default XAMPP
define('DB_PASS', '');           // password default XAMPP (kosong)
define('DB_NAME', 'sistem_mahasiswa');
define('DB_CHARSET', 'utf8mb4');

// Buat koneksi menggunakan MySQLi
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek apakah koneksi berhasil
if ($koneksi->connect_error) {
    die('<div class="alert alert-danger m-3">
            <strong>Koneksi Gagal!</strong> ' . $koneksi->connect_error .
        '</div>');
}

// Set charset agar mendukung karakter unicode (aksara, emoji, dll)
$koneksi->set_charset(DB_CHARSET);

// Mulai sesi PHP jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
