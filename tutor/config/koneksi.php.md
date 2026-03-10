# Catatan: `tutor/config/koneksi.php`

> **Peran file ini:** Menyediakan koneksi ke database MySQL. File ini di-`require` oleh **hampir semua file PHP** di proyek ini—sehingga koneksi hanya ditulis sekali dan dipakai bersama.

---

## Alur Singkat

```
File PHP lain (login, dashboard, dsb.)
        │
        └── require_once '../config/koneksi.php'
                │
                ├── define konstanta host, user, pass, nama DB
                ├── Buat objek $koneksi (MySQLi)
                ├── Cek jika gagal → tampilkan error & stop
                ├── Set charset UTF-8
                └── Mulai sesi jika belum dimulai
```

---

## Penjelasan Per Baris

### Baris 8–12 — Konstanta konfigurasi

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistem_mahasiswa');
define('DB_CHARSET', 'utf8mb4');
```

`define()` membuat **konstanta**—nilai yang tidak bisa diubah setelah dideklarasikan.

| Konstanta    | Nilai                | Keterangan                                |
| ------------ | -------------------- | ----------------------------------------- |
| `DB_HOST`    | `'localhost'`        | Server database ada di komputer yang sama |
| `DB_USER`    | `'root'`             | Username default XAMPP                    |
| `DB_PASS`    | `''`                 | Password kosong (default XAMPP)           |
| `DB_NAME`    | `'sistem_mahasiswa'` | Nama database yang digunakan              |
| `DB_CHARSET` | `'utf8mb4'`          | Mendukung semua karakter Unicode          |

> Menggunakan konstanta (bukan variabel) agar tidak sengaja ditimpa di tempat lain.

---

### Baris 15 — Membuat koneksi MySQLi

```php
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

`mysqli` adalah **ekstensi PHP** untuk berkomunikasi dengan MySQL. `new mysqli(...)` membuat objek koneksi baru. Hasilnya disimpan di `$koneksi`—variabel inilah yang nanti dipakai untuk query di semua file lain.

---

### Baris 18–22 — Cek jika koneksi gagal

```php
if ($koneksi->connect_error) {
    die('<div class="alert alert-danger ...">
            Koneksi Gagal! ' . $koneksi->connect_error .
        '</div>');
}
```

Jika koneksi gagal (misal MySQL belum menyala), `$koneksi->connect_error` berisi pesan error. Fungsi `die()` menampilkan pesan tersebut lalu **menghentikan eksekusi** seluruh program. Ini mencegah error membingungkan di halaman lain.

---

### Baris 25 — Set charset

```php
$koneksi->set_charset(DB_CHARSET);
```

Memastikan data yang dikirim/diterima menggunakan encoding `utf8mb4`. Tanpa ini, karakter di luar ASCII (huruf beraksara, emoji) bisa rusak atau tidak tersimpan dengan benar.

---

### Baris 28–30 — Mulai sesi (jika belum)

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

`session_status()` mengecek apakah sesi sudah berjalan. Kita cek dulu sebelum memanggil `session_start()` karena memanggil `session_start()` dua kali akan menghasilkan error.

> `PHP_SESSION_NONE` adalah konstanta bawaan PHP yang berarti "sesi belum dimulai".

---

## Koneksi ke File Lain

| File yang memakai      | Cara pakai                             |
| ---------------------- | -------------------------------------- |
| `auth/login.php`       | `require_once '../config/koneksi.php'` |
| `auth/logout.php`      | `require_once '../config/koneksi.php'` |
| `auth/register.php`    | `require_once '../config/koneksi.php'` |
| `dashboard/index.php`  | `require_once '../config/koneksi.php'` |
| `dashboard/tambah.php` | `require_once '../config/koneksi.php'` |
| `dashboard/edit.php`   | `require_once '../config/koneksi.php'` |
| `dashboard/hapus.php`  | `require_once '../config/koneksi.php'` |

> `require_once` berbeda dengan `include`: jika file tidak ditemukan, program langsung berhenti (error fatal). `_once` memastikan file hanya dimuat satu kali meski dipanggil berkali-kali.
