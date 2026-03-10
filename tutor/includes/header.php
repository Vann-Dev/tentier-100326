<?php
// =====================================================
// File: includes/header.php
// Deskripsi: Template head HTML yang digunakan
//            bersama di semua halaman
// Parameter: $judul_halaman (string) - judul tab browser
// =====================================================
$judul_halaman = $judul_halaman ?? 'Sistem Mahasiswa';
?>
<!DOCTYPE html>
<html lang="id">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= htmlspecialchars($judul_halaman) ?> — Sistem Mahasiswa</title>

      <!-- Bootstrap 5 CSS via CDN -->
      <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

      <!-- Bootstrap Icons -->
      <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

      <!-- CSS kustom aplikasi -->
      <link rel="stylesheet" href="/tutor/assets/style.css">
</head>

<body class="bg-light">