<?php
// =====================================================
// File: dashboard/edit.php
// Deskripsi: Halaman formulir edit/perbarui data
//            mahasiswa yang sudah ada
// =====================================================
require_once '../config/koneksi.php';

// Cek sesi
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}

// Ambil ID mahasiswa dari parameter URL
$id_mahasiswa = intval($_GET['id'] ?? 0);

// Validasi — ID harus ada dan valid
if ($id_mahasiswa <= 0) {
    header('Location: /tutor/dashboard/');
    exit;
}

// Cari data mahasiswa berdasarkan ID
$stmt_cari = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt_cari->bind_param('i', $id_mahasiswa);
$stmt_cari->execute();
$hasil = $stmt_cari->get_result();

// Jika data tidak ditemukan, kembali ke dashboard
if ($hasil->num_rows === 0) {
    header('Location: /tutor/dashboard/');
    exit;
}

$data_mahasiswa = $hasil->fetch_assoc();
$stmt_cari->close();

$pesan_error = '';

// Proses form ketika dikirim via POST (simpan perubahan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil input dari form
    $nim            = trim($_POST['nim'] ?? '');
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $jurusan        = trim($_POST['jurusan'] ?? '');
    $program_studi  = trim($_POST['program_studi'] ?? '');
    $angkatan       = trim($_POST['angkatan'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $nomor_telepon  = trim($_POST['nomor_telepon'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');

    // Validasi input wajib
    if (
        empty($nim) || empty($nama_lengkap) || empty($jurusan) ||
        empty($program_studi) || empty($angkatan) || empty($jenis_kelamin)
    ) {
        $pesan_error = 'Field bertanda bintang (*) wajib diisi.';
    } elseif (!is_numeric($angkatan) || strlen($angkatan) !== 4) {
        $pesan_error = 'Angkatan harus berupa tahun 4 digit.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = 'Format email tidak valid.';
    } else {
        // Cek apakah NIM sudah dipakai mahasiswa LAIN
        $cek_nim = $koneksi->prepare(
            "SELECT id_mahasiswa FROM mahasiswa WHERE nim = ? AND id_mahasiswa != ?"
        );
        $cek_nim->bind_param('si', $nim, $id_mahasiswa);
        $cek_nim->execute();
        $cek_nim->store_result();

        if ($cek_nim->num_rows > 0) {
            $pesan_error = "NIM $nim sudah dipakai mahasiswa lain.";
        } else {
            // Perbarui data mahasiswa di database
            $stmt = $koneksi->prepare(
                "UPDATE mahasiswa SET
                    nim = ?, nama_lengkap = ?, jurusan = ?, program_studi = ?,
                    angkatan = ?, jenis_kelamin = ?, email = ?,
                    nomor_telepon = ?, alamat = ?
                 WHERE id_mahasiswa = ?"
            );
            $stmt->bind_param(
                'ssssissssi',
                $nim,
                $nama_lengkap,
                $jurusan,
                $program_studi,
                $angkatan,
                $jenis_kelamin,
                $email,
                $nomor_telepon,
                $alamat,
                $id_mahasiswa
            );

            if ($stmt->execute()) {
                header('Location: /tutor/dashboard/?notif=edit');
                exit;
            } else {
                $pesan_error = 'Gagal memperbarui data. Coba lagi.';
            }
            $stmt->close();
        }
        $cek_nim->close();
    }

    // Jika ada error, gunakan input yang baru diketik (bukan data lama)
    if ($pesan_error) {
        $data_mahasiswa = array_merge($data_mahasiswa, $_POST);
    }
}

$judul_halaman = 'Edit Mahasiswa';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-4" style="max-width:760px;">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tutor/dashboard/">Dashboard</a></li>
            <li class="breadcrumb-item active">Edit Mahasiswa</li>
        </ol>
    </nav>

    <h4 class="fw-bold mb-4">
        <i class="bi bi-pencil-square me-2 text-primary"></i>
        Edit Data: <?= htmlspecialchars($data_mahasiswa['nama_lengkap']) ?>
    </h4>

    <!-- Pesan error -->
    <?php if ($pesan_error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($pesan_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="" novalidate>

                <!-- NIM dan Nama -->
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="nim" class="form-label fw-semibold">
                            NIM <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nim" name="nim"
                            maxlength="20" required
                            value="<?= htmlspecialchars($data_mahasiswa['nim']) ?>">
                    </div>
                    <div class="col-md-7">
                        <label for="nama_lengkap" class="form-label fw-semibold">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_lengkap"
                            name="nama_lengkap" maxlength="150" required
                            value="<?= htmlspecialchars($data_mahasiswa['nama_lengkap']) ?>">
                    </div>
                </div>

                <!-- Jurusan dan Program Studi -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="jurusan" class="form-label fw-semibold">
                            Jurusan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan"
                            maxlength="100" required
                            value="<?= htmlspecialchars($data_mahasiswa['jurusan']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="program_studi" class="form-label fw-semibold">
                            Program Studi <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="program_studi"
                            name="program_studi" maxlength="100" required
                            value="<?= htmlspecialchars($data_mahasiswa['program_studi']) ?>">
                    </div>
                </div>

                <!-- Angkatan, Jenis Kelamin, Telepon -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="angkatan" class="form-label fw-semibold">
                            Angkatan <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="angkatan" name="angkatan"
                            min="2000" max="2099" required
                            value="<?= htmlspecialchars($data_mahasiswa['angkatan']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="jenis_kelamin" class="form-label fw-semibold">
                            Jenis Kelamin <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki"
                                <?= $data_mahasiswa['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>
                                Laki-laki
                            </option>
                            <option value="Perempuan"
                                <?= $data_mahasiswa['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>
                                Perempuan
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="nomor_telepon" class="form-label fw-semibold">
                            Nomor Telepon
                        </label>
                        <input type="text" class="form-control" id="nomor_telepon"
                            name="nomor_telepon" maxlength="20"
                            value="<?= htmlspecialchars($data_mahasiswa['nomor_telepon'] ?? '') ?>">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" id="email" name="email" maxlength="150"
                        value="<?= htmlspecialchars($data_mahasiswa['email'] ?? '') ?>">
                </div>

                <!-- Alamat -->
                <div class="mb-4">
                    <label for="alamat" class="form-label fw-semibold">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3"
                        maxlength="500"><?= htmlspecialchars($data_mahasiswa['alamat'] ?? '') ?></textarea>
                </div>

                <!-- Tombol aksi -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Perbarui Data
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