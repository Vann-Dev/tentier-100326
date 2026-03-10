<?php
// =====================================================
// File: auth/logout.php
// Deskripsi: Menghapus sesi pengguna dan mengalihkan
//            ke halaman login
// =====================================================
require_once '../config/koneksi.php';

// Hapus semua variabel sesi
$_SESSION = [];

// Hapus cookie sesi dari browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan sesi sepenuhnya
session_destroy();

// Arahkan pengguna ke halaman login
header('Location: /tutor/auth/login.php?pesan=keluar');
exit;
