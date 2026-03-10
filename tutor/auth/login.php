<?php
// =====================================================
// File: auth/login.php
// Deskripsi: Halaman masuk (login) pengguna
// =====================================================
require_once '../config/koneksi.php';

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/dashboard/');
    exit;
}

$pesan_error = '';  // Tempat menampung pesan error login

// Proses form ketika dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan input dari form
    $email      = trim($_POST['email'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';

    // Validasi: semua field wajib diisi
    if (empty($email) || empty($kata_sandi)) {
        $pesan_error = 'Email dan kata sandi wajib diisi.';
    } else {
        // Cari pengguna berdasarkan email menggunakan prepared statement
        // untuk mencegah SQL Injection
        $stmt = $koneksi->prepare(
            "SELECT id_pengguna, nama_pengguna, kata_sandi FROM pengguna WHERE email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $hasil = $stmt->get_result();

        if ($hasil->num_rows === 1) {
            $data_pengguna = $hasil->fetch_assoc();

            // Verifikasi kata sandi dengan hash yang tersimpan
            if (password_verify($kata_sandi, $data_pengguna['kata_sandi'])) {
                // Login berhasil — simpan data ke sesi
                $_SESSION['id_pengguna']   = $data_pengguna['id_pengguna'];
                $_SESSION['nama_pengguna'] = $data_pengguna['nama_pengguna'];

                // Arahkan ke dashboard
                header('Location: /tutor/dashboard/');
                exit;
            } else {
                $pesan_error = 'Email atau kata sandi salah.';
            }
        } else {
            $pesan_error = 'Email atau kata sandi salah.';
        }

        $stmt->close();
    }
}

$judul_halaman = 'Masuk';
?>
<?php include '../includes/header.php'; ?>

<div class="halaman-auth">
    <div class="kartu-auth">
        <!-- Logo & judul -->
        <div class="text-center mb-4">
            <i class="bi bi-mortarboard-fill text-white" style="font-size:3rem;"></i>
            <h3 class="text-white fw-bold mt-2">Sistem Mahasiswa</h3>
            <p class="text-white-50">Masuk ke akun Anda</p>
        </div>

        <!-- Kartu form login -->
        <div class="card shadow-lg border-0 rounded-4 p-4">

            <!-- Tampilkan pesan error jika ada -->
            <?php if ($pesan_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= htmlspecialchars($pesan_error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <!-- Field email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="contoh@email.com" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- Field kata sandi -->
                <div class="mb-4">
                    <label for="kata_sandi" class="form-label fw-semibold">
                        <i class="bi bi-lock me-1"></i>Kata Sandi
                    </label>
                    <input type="password" class="form-control" id="kata_sandi"
                        name="kata_sandi" placeholder="••••••••" required>
                </div>

                <!-- Tombol masuk -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                    </button>
                </div>
            </form>

            <!-- Link ke halaman daftar -->
            <hr class="my-4">
            <p class="text-center mb-0 text-muted">
                Belum punya akun?
                <a href="/tutor/auth/register.php" class="text-decoration-none fw-semibold">
                    Daftar di sini
                </a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>