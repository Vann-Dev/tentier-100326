# Catatan: `tutor/index.php`

> **Peran file ini:** Pintu masuk utama aplikasi. File ini **tidak menampilkan apapun**—tugasnya hanya memeriksa status login lalu mengarahkan (redirect) pengguna ke halaman yang tepat.

---

## Alur Singkat

```
Buka /tutor/ atau /tutor/index.php
        │
        ├── Sudah login? ──► Masuk ke /tutor/dashboard/
        │
        └── Belum login? ──► Masuk ke /tutor/auth/login.php
```

---

## Penjelasan Per Baris

### Baris 1 — Tag pembuka PHP

```php
<?php
```

Semua kode PHP harus diawali dengan tag `<?php`. Tanpa ini, PHP tidak akan memproses baris berikutnya sebagai kode—melainkan dikirim ke browser sebagai teks biasa.

---

### Baris 8 — Memulai sesi

```php
session_start();
```

**Sesi** adalah cara PHP "mengingat" pengguna antar halaman. Bayangkan sesi seperti loker—setiap pengguna punya lokernya sendiri. `session_start()` membuka (atau membuat) loker tersebut.

> Fungsi ini **wajib dipanggil sebelum** kode lain yang mengakses `$_SESSION`.
> Terhubung ke: semua file yang menggunakan `$_SESSION` (login.php, logout.php, dsb.)

---

### Baris 10–16 — Cek login & redirect

```php
if (isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/dashboard/');
} else {
    header('Location: /tutor/auth/login.php');
}
```

`$_SESSION['id_pengguna']` diisi saat login berhasil (lihat `auth/login.php` baris 41). Di sini kita **cek apakah kunci itu ada**:

| Kondisi                       | Fungsi yang dijalankan    | Tujuan                |
| ----------------------------- | ------------------------- | --------------------- |
| `isset(...)` bernilai `true`  | `header('Location: ...')` | Redirect ke dashboard |
| `isset(...)` bernilai `false` | `header('Location: ...')` | Redirect ke login     |

`header('Location: ...')` bekerja dengan mengirim instruksi HTTP ke browser: _"Pergi ke URL ini."_

---

### Baris 17 — Hentikan eksekusi

```php
exit;
```

Setelah `header()`, PHP **tidak otomatis berhenti**. Baris `exit` memastikan tidak ada kode lain yang berjalan setelah redirect dikirim. Ini adalah praktik wajib setelah `header('Location: ...')`.

---

## Koneksi ke File Lain

| File                  | Hubungan                                   |
| --------------------- | ------------------------------------------ |
| `config/koneksi.php`  | Tidak digunakan di sini (sesi sudah cukup) |
| `auth/login.php`      | Tujuan redirect jika belum login           |
| `dashboard/index.php` | Tujuan redirect jika sudah login           |
