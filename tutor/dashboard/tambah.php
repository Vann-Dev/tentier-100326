<?php
// =====================================================
// File: dashboard/tambah.php
// Deskripsi: Halaman formulir penambahan data
//            mahasiswa baru ke database
// =====================================================
require_once '../config/koneksi.php';

// Cek sesi — hanya pengguna yang login yang bisa akses
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}

$pesan_error = '';

// Proses form ketika dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil semua input dari form dan bersihkan whitespace
    $nim            = trim($_POST['nim'] ?? '');
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $jurusan        = trim($_POST['jurusan'] ?? '');
    $program_studi  = trim($_POST['program_studi'] ?? '');
    $angkatan       = trim($_POST['angkatan'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $nomor_telepon  = trim($_POST['nomor_telepon'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');

    // --- Validasi input wajib ---
    if (
        empty($nim) || empty($nama_lengkap) || empty($jurusan) ||
        empty($program_studi) || empty($angkatan) || empty($jenis_kelamin)
    ) {
        $pesan_error = 'Field bertanda bintang (*) wajib diisi.';
    } elseif (!is_numeric($angkatan) || strlen($angkatan) !== 4) {
        $pesan_error = 'Angkatan harus berupa tahun 4 digit (contoh: 2023).';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = 'Format email tidak valid.';
    } else {
        // Cek apakah NIM sudah ada di database
        $cek_nim = $koneksi->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ?");
        $cek_nim->bind_param('s', $nim);
        $cek_nim->execute();
        $cek_nim->store_result();

        if ($cek_nim->num_rows > 0) {
            $pesan_error = "NIM $nim sudah terdaftar.";
        } else {
            // Simpan data mahasiswa baru ke database
            $stmt = $koneksi->prepare(
                "INSERT INTO mahasiswa
                    (nim, nama_lengkap, jurusan, program_studi, angkatan,
                     jenis_kelamin, email, nomor_telepon, alamat)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'ssssissss',
                $nim,
                $nama_lengkap,
                $jurusan,
                $program_studi,
                $angkatan,
                $jenis_kelamin,
                $email,
                $nomor_telepon,
                $alamat
            );

            if ($stmt->execute()) {
                // Redirect ke dashboard dengan notifikasi sukses
                header('Location: /tutor/dashboard/?notif=tambah');
                exit;
            } else {
                $pesan_error = 'Gagal menyimpan data. Silakan coba lagi.';
            }
            $stmt->close();
        }
        $cek_nim->close();
    }
}

$judul_halaman = 'Tambah Mahasiswa';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-4" style="max-width:760px;">

    <!-- Breadcrumb navigasi -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/tutor/dashboard/">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Tambah Mahasiswa</li>
        </ol>
    </nav>

    <!-- Judul halaman -->
    <h4 class="fw-bold mb-4">
        <i class="bi bi-person-plus me-2 text-primary"></i>Tambah Data Mahasiswa
    </h4>

    <!-- Pesan error -->
    <?php if ($pesan_error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($pesan_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulir tambah mahasiswa -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="" novalidate>

                <!-- Baris 1: NIM dan Nama -->
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="nim" class="form-label fw-semibold">
                            NIM <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nim" name="nim"
                            placeholder="Contoh: 2024001001" maxlength="20"
                            value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-7">
                        <label for="nama_lengkap" class="form-label fw-semibold">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_lengkap"
                            name="nama_lengkap" placeholder="Nama lengkap mahasiswa" maxlength="150"
                            value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Baris 2: Jurusan dan Program Studi -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="jurusan" class="form-label fw-semibold">
                            Jurusan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan"
                            placeholder="Contoh: Teknik Informatika" maxlength="100"
                            value="<?= htmlspecialchars($_POST['jurusan'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="program_studi" class="form-label fw-semibold">
                            Program Studi <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="program_studi"
                            name="program_studi" placeholder="Contoh: S1 Teknik Informatika" maxlength="100"
                            value="<?= htmlspecialchars($_POST['program_studi'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Baris 3: Angkatan dan Jenis Kelamin -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="angkatan" class="form-label fw-semibold">
                            Angkatan <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="angkatan" name="angkatan"
                            placeholder="Contoh: 2024" min="2000" max="2099"
                            value="<?= htmlspecialchars($_POST['angkatan'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="jenis_kelamin" class="form-label fw-semibold">
                            Jenis Kelamin <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki"
                                <?= ($_POST['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>
                                Laki-laki
                            </option>
                            <option value="Perempuan"
                                <?= ($_POST['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>
                                Perempuan
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="nomor_telepon" class="form-label fw-semibold">
                            Nomor Telepon
                        </label>
                        <input type="text" class="form-control" id="nomor_telepon"
                            name="nomor_telepon" placeholder="08xxxxxxxxxx" maxlength="20"
                            value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>">
                    </div>
                </div>

                <!-- Baris 4: Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="email@contoh.com" maxlength="150"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- Baris 5: Alamat -->
                <div class="mb-4">
                    <label for="alamat" class="form-label fw-semibold">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat"
                        rows="3" placeholder="Alamat lengkap mahasiswa"
                        maxlength="500"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                </div>

                <!-- Tombol aksi -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Data
                    </button>
                    <a href="/tutor/dashboard/" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>