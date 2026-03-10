# Catatan: `tutor/dashboard/tambah.php`

> **Peran file ini:** Menampilkan form untuk menambah data mahasiswa baru dan menyimpannya ke database. Hanya bisa diakses oleh pengguna yang sudah login.

---

## Alur Singkat

```
Pengguna klik "Tambah Mahasiswa" di dashboard
        │
        ├── Belum login? ──► Redirect ke login
        │
        └── Sudah login:
                │
                ├── Baru buka (GET) ──► Tampilkan form kosong
                │
                └── Klik "Simpan Data" (POST):
                        │
                        ├── Validasi: field wajib diisi?
                        ├── Validasi: angkatan 4 digit?
                        ├── Validasi: format email valid?
                        ├── Cek: NIM sudah ada?
                        │
                        ├── Ada masalah ──► Tampilkan error, isi ulang form
                        │
                        └── Semua valid:
                                └── INSERT ke database
                                    Redirect ke dashboard?notif=tambah
```

---

## Penjelasan Per Baris

### Baris 7 — Muat koneksi

```php
require_once '../config/koneksi.php';
```

Menyediakan `$koneksi` dan `$_SESSION`.

---

### Baris 10–13 — Proteksi halaman

```php
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}
```

Pola standar proteksi—sama persis dengan `dashboard/index.php`. Setiap halaman dashboard wajib memiliki blok ini.

---

### Baris 20–28 — Ambil input dari form

```php
$nim           = trim($_POST['nim'] ?? '');
$nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
$jurusan       = trim($_POST['jurusan'] ?? '');
$program_studi = trim($_POST['program_studi'] ?? '');
$angkatan      = trim($_POST['angkatan'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$email         = trim($_POST['email'] ?? '');
$nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');
```

Sembilan input diambil dari `$_POST`. Semua di-`trim()` kecuali `jenis_kelamin` (nilai pasti dari dropdown, tidak perlu dibersihkan).

---

### Baris 31–39 — Validasi input wajib

```php
if (
    empty($nim) || empty($nama_lengkap) || empty($jurusan) ||
    empty($program_studi) || empty($angkatan) || empty($jenis_kelamin)
) {
    $pesan_error = 'Field bertanda bintang (*) wajib diisi.';
} elseif (!is_numeric($angkatan) || strlen($angkatan) !== 4) {
    $pesan_error = 'Angkatan harus berupa tahun 4 digit (contoh: 2023).';
} elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $pesan_error = 'Format email tidak valid.';
```

Validasi bertahap. Perhatikan baris validasi email: `$email !== '' &&` artinya email hanya divalidasi formatnya **jika diisi**. Email adalah field opsional—boleh kosong.

---

### Baris 42–45 — Cek NIM duplikat

```php
$cek_nim = $koneksi->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ?");
$cek_nim->bind_param('s', $nim);
$cek_nim->execute();
$cek_nim->store_result();
```

NIM (Nomor Induk Mahasiswa) harus unik. Sebelum menyimpan, kita cari apakah NIM ini sudah ada. Prepared statement digunakan agar aman dari SQL Injection.

---

### Baris 51–68 — INSERT data ke database

```php
$stmt = $koneksi->prepare(
    "INSERT INTO mahasiswa
        (nim, nama_lengkap, jurusan, program_studi, angkatan,
         jenis_kelamin, email, nomor_telepon, alamat)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'ssssissss',
    $nim, $nama_lengkap, $jurusan, $program_studi,
    $angkatan, $jenis_kelamin, $email, $nomor_telepon, $alamat
);
```

`INSERT INTO` menambahkan baris baru ke tabel `mahasiswa`. Perhatikan tipe di `bind_param`:

| Karakter | Tipe    | Kolom         |
| -------- | ------- | ------------- |
| `s`      | string  | nim           |
| `s`      | string  | nama_lengkap  |
| `s`      | string  | jurusan       |
| `s`      | string  | program_studi |
| `i`      | integer | angkatan      |
| `s`      | string  | jenis_kelamin |
| `s`      | string  | email         |
| `s`      | string  | nomor_telepon |
| `s`      | string  | alamat        |

---

### Baris 70–73 — Redirect setelah berhasil

```php
if ($stmt->execute()) {
    header('Location: /tutor/dashboard/?notif=tambah');
    exit;
}
```

Setelah data berhasil disimpan, redirect ke dashboard dengan parameter `?notif=tambah`. Di `dashboard/index.php` baris 66, parameter ini dibaca untuk menampilkan notifikasi hijau "Data berhasil ditambahkan."

---

### Baris 125–127 — Pertahankan nilai form setelah error

```php
<input ... value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>">
```

Saat ada error dan halaman dimuat ulang, nilai yang sudah diketik pengguna diisi kembali ke field. Ini meningkatkan pengalaman pengguna—tidak perlu ketik ulang dari awal. `htmlspecialchars()` melindungi dari XSS.

---

## Koneksi ke File Lain

| File                           | Hubungan                                                                                  |
| ------------------------------ | ----------------------------------------------------------------------------------------- |
| `config/koneksi.php`           | Sumber `$koneksi` dan `$_SESSION`                                                         |
| `includes/header.php`          | Template HTML pembuka                                                                     |
| `includes/navbar.php`          | Navigasi atas                                                                             |
| `dashboard/index.php`          | Tujuan redirect setelah berhasil simpan (baris 72); sumber link tombol "Tambah Mahasiswa" |
| **Tabel database** `mahasiswa` | `INSERT` di baris 52 menambah baris baru                                                  |
