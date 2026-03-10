<?php
// =====================================================
// File: dashboard/index.php
// Deskripsi: Halaman utama dashboard — menampilkan
//            daftar semua mahasiswa + statistik
// =====================================================
require_once '../config/koneksi.php';

// Cek apakah pengguna sudah login; jika tidak, alihkan ke login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}

// --- Statistik ringkasan ---
// Jumlah total mahasiswa
$total_mahasiswa = $koneksi->query("SELECT COUNT(*) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];

// Jumlah prodi unik yang terdaftar
$total_prodi = $koneksi->query("SELECT COUNT(DISTINCT program_studi) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];

// Jumlah angkatan yang terdaftar
$total_angkatan = $koneksi->query("SELECT COUNT(DISTINCT angkatan) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];

// --- Filter dan pencarian ---
$kata_cari   = trim($_GET['cari'] ?? '');   // kata kunci pencarian
$filter_prodi = trim($_GET['prodi'] ?? ''); // filter program studi

// Ambil daftar program studi untuk dropdown filter
$daftar_prodi = $koneksi->query(
    "SELECT DISTINCT program_studi FROM mahasiswa ORDER BY program_studi ASC"
);

// --- Query daftar mahasiswa dengan filter dinamis ---
$kondisi = [];
$params  = [];
$tipe    = '';

if ($kata_cari !== '') {
    $kondisi[] = "(nim LIKE ? OR nama_lengkap LIKE ? OR jurusan LIKE ?)";
    $pencarian = "%$kata_cari%";
    $params    = array_merge($params, [$pencarian, $pencarian, $pencarian]);
    $tipe     .= 'sss';
}

if ($filter_prodi !== '') {
    $kondisi[] = "program_studi = ?";
    $params[]  = $filter_prodi;
    $tipe     .= 's';
}

$klausa_where = count($kondisi) ? 'WHERE ' . implode(' AND ', $kondisi) : '';
$sql = "SELECT * FROM mahasiswa $klausa_where ORDER BY dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
if ($params) {
    $stmt->bind_param($tipe, ...$params);
}
$stmt->execute();
$daftar_mahasiswa = $stmt->get_result();

// Ambil pesan notifikasi dari URL (setelah tambah/edit/hapus)
$notif = $_GET['notif'] ?? '';

$judul_halaman = 'Dashboard';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container-fluid py-4 px-4">

    <!-- Notifikasi aksi berhasil -->
    <?php if ($notif === 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> Data mahasiswa berhasil ditambahkan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($notif === 'edit'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-pencil-check me-1"></i> Data mahasiswa berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($notif === 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-trash me-1"></i> Data mahasiswa berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Judul halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
            </h4>
            <p class="text-muted mb-0">Kelola data mahasiswa</p>
        </div>
        <a href="/tutor/dashboard/tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Tambah Mahasiswa
        </a>
    </div>

    <!-- Kartu statistik -->
    <div class="row g-3 mb-4">
        <!-- Total mahasiswa -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-primary mb-2" style="font-size:2rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h2 class="fw-bold"><?= $total_mahasiswa ?></h2>
                <p class="text-muted mb-0">Total Mahasiswa</p>
            </div>
        </div>
        <!-- Total program studi -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-success mb-2" style="font-size:2rem;">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <h2 class="fw-bold"><?= $total_prodi ?></h2>
                <p class="text-muted mb-0">Program Studi</p>
            </div>
        </div>
        <!-- Total angkatan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-warning mb-2" style="font-size:2rem;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h2 class="fw-bold"><?= $total_angkatan ?></h2>
                <p class="text-muted mb-0">Angkatan</p>
            </div>
        </div>
    </div>

    <!-- Form filter & pencarian -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <!-- Input pencarian -->
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search me-1"></i>Cari
                    </label>
                    <input type="text" class="form-control" name="cari"
                        placeholder="NIM, nama, atau jurusan..."
                        value="<?= htmlspecialchars($kata_cari) ?>">
                </div>
                <!-- Dropdown filter prodi -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel me-1"></i>Program Studi
                    </label>
                    <select class="form-select" name="prodi">
                        <option value="">-- Semua Program Studi --</option>
                        <?php while ($baris_prodi = $daftar_prodi->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($baris_prodi['program_studi']) ?>"
                                <?= $filter_prodi === $baris_prodi['program_studi'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($baris_prodi['program_studi']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Tombol aksi filter -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="/tutor/dashboard/" class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel daftar mahasiswa -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold py-3">
            <i class="bi bi-table me-1 text-primary"></i>
            Daftar Mahasiswa
            <?php if ($kata_cari || $filter_prodi): ?>
                <span class="badge bg-info ms-2">Hasil filter</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if ($daftar_mahasiswa->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 tabel-mahasiswa">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Jurusan / Prodi</th>
                                <th>Angkatan</th>
                                <th>Jenis Kelamin</th>
                                <th>Kontak</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $nomor = 1;
                            while ($mhs = $daftar_mahasiswa->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-muted"><?= $nomor++ ?></td>
                                    <td>
                                        <code><?= htmlspecialchars($mhs['nim']) ?></code>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($mhs['nama_lengkap']) ?>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">
                                            <?= htmlspecialchars($mhs['jurusan']) ?>
                                        </small>
                                        <?= htmlspecialchars($mhs['program_studi']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary badge-angkatan">
                                            <?= htmlspecialchars($mhs['angkatan']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($mhs['jenis_kelamin'] === 'Laki-laki'): ?>
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-gender-male"></i> Laki-laki
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-pink" style="background:#e83e8c;color:white;">
                                                <i class="bi bi-gender-female"></i> Perempuan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($mhs['email']): ?>
                                            <small class="d-block">
                                                <i class="bi bi-envelope text-muted"></i>
                                                <?= htmlspecialchars($mhs['email']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($mhs['nomor_telepon']): ?>
                                            <small class="d-block">
                                                <i class="bi bi-telephone text-muted"></i>
                                                <?= htmlspecialchars($mhs['nomor_telepon']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- Tombol edit -->
                                        <a href="/tutor/dashboard/edit.php?id=<?= $mhs['id_mahasiswa'] ?>"
                                            class="btn btn-sm btn-outline-primary me-1"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Tombol hapus dengan konfirmasi -->
                                        <a href="/tutor/dashboard/hapus.php?id=<?= $mhs['id_mahasiswa'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data <?= addslashes($mhs['nama_lengkap']) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Tampilkan pesan jika tidak ada data -->
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size:3rem;"></i>
                    <p class="mt-2">Tidak ada data mahasiswa ditemukan.</p>
                    <a href="/tutor/dashboard/tambah.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Tambah Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>