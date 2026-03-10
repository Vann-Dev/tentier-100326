# Catatan: `tutor/auth/login.php`

> **Peran file ini:** Menangani proses masuk (login) pengguna. File ini melakukan dua hal sekaligus: (1) menampilkan form login, dan (2) memproses data yang dikirim form tersebut.

---

## Alur Singkat

```
Pengguna buka halaman login
        │
        ├── Sudah login? ──► Redirect ke dashboard (baris 9–12)
        │
        └── Belum login:
                │
                ├── Baru buka (GET) ──► Tampilkan form kosong
                │
                └── Klik tombol Masuk (POST):
                        │
                        ├── Field kosong? ──► Tampilkan error
                        │
                        ├── Cari email di database
                        │
                        ├── Email tidak ada? ──► Tampilkan error
                        │
                        ├── Password salah? ──► Tampilkan error
                        │
                        └── Semua benar ──► Simpan ke $_SESSION
                                           Redirect ke dashboard
```

---

## Penjelasan Per Baris

### Baris 6 — Muat koneksi database

```php
require_once '../config/koneksi.php';
```

Memuat file `koneksi.php` yang menyediakan variabel `$koneksi` (objek MySQLi) dan memastikan sesi sudah dimulai. Tanda `../` artinya naik satu folder (dari `auth/` ke `tutor/`).

---

### Baris 9–12 — Cegah akses ganda

```php
if (isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/dashboard/');
    exit;
}
```

Jika pengguna sudah login (ada `$_SESSION['id_pengguna']`), tidak perlu ke halaman login lagi—langsung redirect ke dashboard. Ini mencegah pengguna yang sudah login mengisi form login ulang.

---

### Baris 14 — Variabel pesan error

```php
$pesan_error = '';
```

Membuat variabel kosong sebagai "wadah" pesan error. Jika login gagal, variabel ini diisi pesan. Jika berhasil, tetap kosong.

---

### Baris 17 — Deteksi metode pengiriman form

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

`$_SERVER['REQUEST_METHOD']` berisi metode HTTP yang digunakan:

- `'GET'` → halaman baru dibuka (belum ada yang dikirim)
- `'POST'` → form sudah dikirim (tombol "Masuk" diklik)

Blok ini hanya dijalankan ketika form dikirim.

---

### Baris 19–20 — Ambil input dari form

```php
$email      = trim($_POST['email'] ?? '');
$kata_sandi = $_POST['kata_sandi'] ?? '';
```

`$_POST` adalah array yang berisi semua data yang dikirim form. `trim()` menghapus spasi di awal/akhir. Operator `??` (null coalescing) memberi nilai default `''` jika kunci tidak ada—mencegah error "undefined index".

---

### Baris 23–25 — Validasi: field tidak boleh kosong

```php
if (empty($email) || empty($kata_sandi)) {
    $pesan_error = 'Email dan kata sandi wajib diisi.';
```

`empty()` bernilai `true` jika variabel kosong, `null`, atau `0`. `||` berarti "atau"—jika salah satu kosong, langsung set pesan error.

---

### Baris 28–33 — Prepared Statement: cari email di database

```php
$stmt = $koneksi->prepare(
    "SELECT id_pengguna, nama_pengguna, kata_sandi FROM pengguna WHERE email = ?"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$hasil = $stmt->get_result();
```

**Prepared statement** adalah cara aman mengirim query ke database—tanda `?` adalah placeholder yang nanti diisi oleh `bind_param`.

| Langkah                   | Penjelasan                                            |
| ------------------------- | ----------------------------------------------------- |
| `prepare(...)`            | Siapkan template query dengan `?` sebagai placeholder |
| `bind_param('s', $email)` | Isi `?` dengan `$email`. Huruf `'s'` = tipe string    |
| `execute()`               | Jalankan query ke database                            |
| `get_result()`            | Ambil hasil query                                     |

> Kenapa tidak langsung `"... WHERE email = '$email'"`? Karena itu rentan **SQL Injection**—pengguna jahat bisa memasukkan kode SQL lewat form.

---

### Baris 35–36 — Cek apakah email ditemukan

```php
if ($hasil->num_rows === 1) {
    $data_pengguna = $hasil->fetch_assoc();
```

`num_rows` berisi jumlah baris yang ditemukan. Kita cek `=== 1` karena email harus unik. `fetch_assoc()` mengambil satu baris sebagai array asosiatif—misal `$data_pengguna['nama_pengguna']`.

---

### Baris 39–46 — Verifikasi password

```php
if (password_verify($kata_sandi, $data_pengguna['kata_sandi'])) {
    $_SESSION['id_pengguna']   = $data_pengguna['id_pengguna'];
    $_SESSION['nama_pengguna'] = $data_pengguna['nama_pengguna'];
    header('Location: /tutor/dashboard/');
    exit;
```

`password_verify()` membandingkan password yang diketik dengan hash yang tersimpan di database. Password di database **tidak pernah disimpan polos**—selalu di-hash menggunakan `password_hash()` saat registrasi.

Jika cocok:

1. Simpan id dan nama pengguna ke `$_SESSION` (agar halaman lain tahu siapa yang login)
2. Redirect ke dashboard

---

### Baris 58–59 — Siapkan variabel untuk HTML

```php
$judul_halaman = 'Masuk';
```

Variabel ini dikirim ke `includes/header.php` untuk mengisi tag `<title>` di browser.

---

### Baris 60–61 — Muat template HTML

```php
<?php include '../includes/header.php'; ?>
```

Menyisipkan konten `header.php` (tag `<html>`, `<head>`, dll.) tanpa harus menulis ulang di setiap file.

---

### Baris 75–81 — Tampilkan pesan error (jika ada)

```php
<?php if ($pesan_error): ?>
    <div class="alert alert-danger ...">
        <?= htmlspecialchars($pesan_error) ?>
    </div>
<?php endif; ?>
```

`<?= ... ?>` adalah shorthand untuk `<?php echo ... ?>`. `htmlspecialchars()` mengubah karakter seperti `<`, `>`, `&` menjadi entitas HTML—mencegah **XSS (Cross-Site Scripting)**.

---

### Baris 83–109 — Form HTML

```php
<form method="POST" action="" novalidate>
```

`method="POST"` → data dikirim lewat body HTTP (tidak terlihat di URL). `action=""` → kirim ke halaman yang sama (login.php itu sendiri). `novalidate` → matikan validasi bawaan browser agar kita bisa menangani validasi sendiri di PHP.

---

## Koneksi ke File Lain

| File                          | Hubungan                                                        |
| ----------------------------- | --------------------------------------------------------------- |
| `config/koneksi.php`          | Di-`require` di baris 6, menyediakan `$koneksi` dan `$_SESSION` |
| `includes/header.php`         | Di-`include` di baris 60, menyediakan tag HTML pembuka          |
| `auth/register.php`           | Link "Daftar di sini" di baris 115                              |
| `dashboard/index.php`         | Tujuan redirect setelah login berhasil (baris 45)               |
| **Tabel database** `pengguna` | Query `SELECT` di baris 29 membaca data dari tabel ini          |
