<?php
// =====================================================
// File: auth/register.php
// Deskripsi: Halaman pendaftaran akun pengguna baru
// =====================================================
require_once '../config/koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/dashboard/');
    exit;
}

$pesan_error   = '';
$pesan_sukses  = '';

// Proses form ketika dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan semua input
    $nama_pengguna       = trim($_POST['nama_pengguna'] ?? '');
    $email               = trim($_POST['email'] ?? '');
    $kata_sandi          = $_POST['kata_sandi'] ?? '';
    $konfirmasi_sandi    = $_POST['konfirmasi_sandi'] ?? '';

    // --- Validasi input ---
    if (empty($nama_pengguna) || empty($email) || empty($kata_sandi)) {
        $pesan_error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = 'Format email tidak valid.';
    } elseif (strlen($kata_sandi) < 6) {
        $pesan_error = 'Kata sandi minimal 6 karakter.';
    } elseif ($kata_sandi !== $konfirmasi_sandi) {
        $pesan_error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        // Cek apakah email sudah terdaftar
        $cek = $koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE email = ?");
        $cek->bind_param('s', $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $pesan_error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            // Hash kata sandi sebelum disimpan ke database
            $sandi_ter_hash = password_hash($kata_sandi, PASSWORD_BCRYPT);

            // Simpan pengguna baru ke database
            $stmt = $koneksi->prepare(
                "INSERT INTO pengguna (nama_pengguna, email, kata_sandi) VALUES (?, ?, ?)"
            );
            $stmt->bind_param('sss', $nama_pengguna, $email, $sandi_ter_hash);

            if ($stmt->execute()) {
                $pesan_sukses = 'Akun berhasil dibuat! Silakan masuk.';
            } else {
                $pesan_error = 'Terjadi kesalahan. Coba lagi.';
            }

            $stmt->close();
        }
        $cek->close();
    }
}

$judul_halaman = 'Daftar';
?>
<?php include '../includes/header.php'; ?>

<div class="halaman-auth">
    <div class="kartu-auth">
        <!-- Logo & judul -->
        <div class="text-center mb-4">
            <i class="bi bi-mortarboard-fill text-white" style="font-size:3rem;"></i>
            <h3 class="text-white fw-bold mt-2">Sistem Mahasiswa</h3>
            <p class="text-white-50">Buat akun baru</p>
        </div>

        <!-- Kartu form register -->
        <div class="card shadow-lg border-0 rounded-4 p-4">

            <!-- Pesan error -->
            <?php if ($pesan_error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= htmlspecialchars($pesan_error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Pesan sukses -->
            <?php if ($pesan_sukses): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i>
                    <?= htmlspecialchars($pesan_sukses) ?>
                    <a href="/tutor/auth/login.php" class="fw-bold">Klik di sini untuk masuk.</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <!-- Nama lengkap pengguna -->
                <div class="mb-3">
                    <label for="nama_pengguna" class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Nama Lengkap
                    </label>
                    <input type="text" class="form-control" id="nama_pengguna"
                        name="nama_pengguna" placeholder="Nama lengkap Anda" required
                        value="<?= htmlspecialchars($_POST['nama_pengguna'] ?? '') ?>">
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="contoh@email.com" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- Kata sandi -->
                <div class="mb-3">
                    <label for="kata_sandi" class="form-label fw-semibold">
                        <i class="bi bi-lock me-1"></i>Kata Sandi
                    </label>
                    <input type="password" class="form-control" id="kata_sandi"
                        name="kata_sandi" placeholder="Minimal 6 karakter" required>
                    <div class="form-text">Minimal 6 karakter.</div>
                </div>

                <!-- Konfirmasi kata sandi -->
                <div class="mb-4">
                    <label for="konfirmasi_sandi" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill me-1"></i>Konfirmasi Kata Sandi
                    </label>
                    <input type="password" class="form-control" id="konfirmasi_sandi"
                        name="konfirmasi_sandi" placeholder="Ulangi kata sandi" required>
                </div>

                <!-- Tombol daftar -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                        <i class="bi bi-person-plus me-1"></i>Daftar
                    </button>
                </div>
            </form>

            <!-- Link kembali ke login -->
            <hr class="my-4">
            <p class="text-center mb-0 text-muted">
                Sudah punya akun?
                <a href="/tutor/auth/login.php" class="text-decoration-none fw-semibold">
                    Masuk di sini
                </a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>