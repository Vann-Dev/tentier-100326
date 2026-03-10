<?php
// =====================================================
// File: dashboard/hapus.php
// Deskripsi: Menghapus data mahasiswa dari database
//            berdasarkan ID, lalu redirect ke dashboard
// =====================================================
require_once '../config/koneksi.php';

// Cek sesi
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}

// Ambil dan validasi ID dari parameter URL
$id_mahasiswa = intval($_GET['id'] ?? 0);

if ($id_mahasiswa > 0) {
    // Pastikan data ada sebelum dihapus
    $cek = $koneksi->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE id_mahasiswa = ?");
    $cek->bind_param('i', $id_mahasiswa);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Hapus data mahasiswa dari database
        $stmt = $koneksi->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
        $stmt->bind_param('i', $id_mahasiswa);
        $stmt->execute();
        $stmt->close();

        // Redirect ke dashboard dengan notifikasi berhasil
        header('Location: /tutor/dashboard/?notif=hapus');
        exit;
    }
    $cek->close();
}

// Jika ID tidak valid atau data tidak ditemukan, kembali ke dashboard
header('Location: /tutor/dashboard/');
exit;
