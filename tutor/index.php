<?php
// =====================================================
// File: index.php
// Deskripsi: Titik masuk utama aplikasi.
//            Mengarahkan ke dashboard jika sudah login,
//            atau ke halaman login jika belum.
// =====================================================
session_start();

if (isset($_SESSION['id_pengguna'])) {
    // Pengguna sudah login — arahkan ke dashboard
    header('Location: /tutor/dashboard/');
} else {
    // Belum login — arahkan ke halaman masuk
    header('Location: /tutor/auth/login.php');
}
exit;
