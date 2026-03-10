<?php
// =====================================================
// File: includes/navbar.php
// Deskripsi: Navigasi atas yang ditampilkan setelah
//            pengguna berhasil login
// =====================================================
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Nama aplikasi / brand -->
        <a class="navbar-brand fw-bold" href="/tutor/dashboard/">
            <i class="bi bi-mortarboard-fill me-2"></i>Sistem Mahasiswa
        </a>

        <!-- Tombol toggle untuk layar kecil -->
        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarUtama">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Item navigasi -->
        <div class="collapse navbar-collapse" id="navbarUtama">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/tutor/dashboard/">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tutor/dashboard/tambah.php">
                        <i class="bi bi-person-plus me-1"></i>Tambah Mahasiswa
                    </a>
                </li>
            </ul>

            <!-- Info pengguna yang sedang login & tombol logout -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['nama_pengguna'] ?? 'Pengguna') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger" href="/tutor/auth/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i>Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>