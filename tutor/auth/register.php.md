# Catatan: `tutor/auth/register.php`

> **Peran file ini:** Menangani pendaftaran akun pengguna baru. Seperti `login.php`, file ini menampilkan form **dan** memproses data yang dikirim dalam satu file.

---

## Alur Singkat

```
Pengguna buka halaman daftar
        │
        ├── Sudah login? ──► Redirect ke dashboard
        │
        └── Belum login:
                │
                ├── Baru buka (GET) ──► Tampilkan form kosong
                │
                └── Klik tombol Daftar (POST):
                        │
                        ├── Validasi: field kosong?
                        ├── Validasi: format email valid?
                        ├── Validasi: password minimal 6 karakter?
                        ├── Validasi: konfirmasi password cocok?
                        ├── Cek: email sudah terdaftar?
                        │
                        ├── Ada masalah ──► Tampilkan error
                        │
                        └── Semua valid:
                                ├── Hash password
                                ├── Simpan ke database
                                └── Tampilkan pesan sukses
```

---

## Penjelasan Per Baris

### Baris 6 — Muat koneksi

```php
require_once '../config/koneksi.php';
```

Menyediakan `$koneksi` untuk query database dan memulai sesi.

---

### Baris 9–12 — Cegah akses ganda

```php
if (isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/dashboard/');
    exit;
}
```

Pengguna yang sudah login tidak perlu mendaftar lagi—langsung diarahkan ke dashboard.

---

### Baris 14–15 — Variabel pesan

```php
$pesan_error  = '';
$pesan_sukses = '';
```

Dua variabel wadah pesan: satu untuk error, satu untuk sukses. Hanya satu yang akan berisi teks setelah proses selesai.

---

### Baris 18 — Cek metode POST

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

Sama seperti di `login.php`—blok ini hanya berjalan ketika form dikirim.

---

### Baris 20–23 — Ambil input dari form

```php
$nama_pengguna    = trim($_POST['nama_pengguna'] ?? '');
$email            = trim($_POST['email'] ?? '');
$kata_sandi       = $_POST['kata_sandi'] ?? '';
$konfirmasi_sandi = $_POST['konfirmasi_sandi'] ?? '';
```

Ambil empat input dari form. `trim()` dipakai pada teks (bukan password) untuk menghapus spasi tidak disengaja. Password tidak di-trim agar karakter spasi di dalamnya tetap terjaga.

---

### Baris 26–33 — Rantai validasi

```php
if (empty($nama_pengguna) || empty($email) || empty($kata_sandi)) {
    $pesan_error = 'Semua field wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $pesan_error = 'Format email tidak valid.';
} elseif (strlen($kata_sandi) < 6) {
    $pesan_error = 'Kata sandi minimal 6 karakter.';
} elseif ($kata_sandi !== $konfirmasi_sandi) {
    $pesan_error = 'Konfirmasi kata sandi tidak cocok.';
```

Validasi dilakukan berurutan dari atas ke bawah. `elseif` memastikan hanya satu pesan error yang muncul (bukan sekaligus).

| Validasi         | Fungsi yang dipakai                      | Penjelasan                                                   |
| ---------------- | ---------------------------------------- | ------------------------------------------------------------ |
| Field kosong     | `empty()`                                | Cek variabel kosong                                          |
| Format email     | `filter_var(..., FILTER_VALIDATE_EMAIL)` | Validasi format email (ada `@`, domain, dll.)                |
| Panjang password | `strlen()`                               | Hitung jumlah karakter                                       |
| Konfirmasi cocok | `!==`                                    | Perbandingan ketat (persis sama, termasuk huruf besar/kecil) |

---

### Baris 36–39 — Cek email duplikat di database

```php
$cek = $koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE email = ?");
$cek->bind_param('s', $email);
$cek->execute();
$cek->store_result();
```

Sebelum menyimpan, kita pastikan email belum pernah didaftarkan. `store_result()` menyimpan hasil query ke memori sehingga `num_rows` bisa dibaca.

---

### Baris 45 — Hash password

```php
$sandi_ter_hash = password_hash($kata_sandi, PASSWORD_BCRYPT);
```

**Wajib dipahami:** Password tidak boleh disimpan dalam bentuk aslinya di database. `password_hash()` mengubah password menjadi string acak (hash) yang tidak bisa dibaca balik. `PASSWORD_BCRYPT` adalah algoritma hashing yang direkomendasikan.

> Hash yang dihasilkan nantinya diverifikasi di `login.php` menggunakan `password_verify()`.

---

### Baris 48–51 — Simpan data ke database

```php
$stmt = $koneksi->prepare(
    "INSERT INTO pengguna (nama_pengguna, email, kata_sandi) VALUES (?, ?, ?)"
);
$stmt->bind_param('sss', $nama_pengguna, $email, $sandi_ter_hash);
```

`INSERT INTO` menambahkan baris baru ke tabel `pengguna`. Tiga `?` diisi dengan tiga variabel—tipe `'sss'` artinya ketiganya adalah string.

---

### Baris 53–57 — Proses sukses/gagal

```php
if ($stmt->execute()) {
    $pesan_sukses = 'Akun berhasil dibuat! Silakan masuk.';
} else {
    $pesan_error = 'Terjadi kesalahan. Coba lagi.';
}
```

`execute()` mengembalikan `true` jika berhasil. Jika berhasil, isi `$pesan_sukses`. Perhatikan: **tidak langsung redirect**—pengguna disuruh klik link login secara manual (lebih jelas bagi pemula).

---

## Koneksi ke File Lain

| File                          | Hubungan                                                                               |
| ----------------------------- | -------------------------------------------------------------------------------------- |
| `config/koneksi.php`          | Di-`require` di baris 6                                                                |
| `includes/header.php`         | Di-`include` di baris 67                                                               |
| `auth/login.php`              | Link "Masuk di sini" di baris 151; password yang di-hash di sini diverifikasi di login |
| **Tabel database** `pengguna` | `INSERT` di baris 49 menyimpan pengguna baru                                           |
