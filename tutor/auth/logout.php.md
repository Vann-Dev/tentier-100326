# Catatan: `tutor/auth/logout.php`

> **Peran file ini:** Menghapus sesi pengguna secara lengkap lalu mengarahkan kembali ke halaman login. File ini **tidak menampilkan halaman apapun**—hanya berisi logika PHP murni.

---

## Alur Singkat

```
Pengguna klik "Keluar" (dari navbar.php)
        │
        └── logout.php
                │
                ├── Kosongkan $_SESSION
                ├── Hapus cookie sesi dari browser
                ├── Hancurkan sesi di server
                └── Redirect ke login.php?pesan=keluar
```

---

## Penjelasan Per Baris

### Baris 7 — Muat koneksi (dan sesi)

```php
require_once '../config/koneksi.php';
```

File ini di-`require` karena `koneksi.php` juga memanggil `session_start()`. Kita perlu sesi aktif dulu sebelum bisa menghapusnya.

---

### Baris 10 — Kosongkan semua variabel sesi

```php
$_SESSION = [];
```

`$_SESSION` adalah array superglobal. Dengan mengisinya dengan array kosong `[]`, semua data sesi (id, nama pengguna, dll.) langsung terhapus dari memori PHP.

---

### Baris 13–24 — Hapus cookie sesi dari browser

```php
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
```

Sesi PHP disimpan dalam dua tempat: **di server** (file/memori) dan **di browser** (sebagai cookie bernama `PHPSESSID`). Langkah di atas menghapus cookie dari browser dengan cara menyetel ulang cookie tersebut dengan waktu kedaluwarsa di masa lalu (`time() - 42000`). Browser otomatis menghapus cookie yang sudah kedaluwarsa.

| Parameter `setcookie` | Keterangan                                                  |
| --------------------- | ----------------------------------------------------------- |
| `session_name()`      | Nama cookie sesi (biasanya `"PHPSESSID"`)                   |
| `''`                  | Nilai dikosongkan                                           |
| `time() - 42000`      | Waktu kedaluwarsa = masa lalu → browser hapus cookie        |
| `$params[...]`        | Samakan pengaturan path, domain, dll. dengan cookie aslinya |

---

### Baris 27 — Hancurkan sesi di server

```php
session_destroy();
```

Setelah cookie dibersihkan, `session_destroy()` menghapus data sesi yang tersimpan di **sisi server**. Tanpa ini, data sesi lama masih tersimpan di server meski cookienya sudah dihapus.

> Urutan yang benar: `$_SESSION = []` → hapus cookie → `session_destroy()`.

---

### Baris 30–31 — Redirect ke halaman login

```php
header('Location: /tutor/auth/login.php?pesan=keluar');
exit;
```

Setelah logout selesai, pengguna diarahkan ke login. Parameter `?pesan=keluar` dikirim di URL—bisa digunakan di `login.php` untuk menampilkan notifikasi "Anda telah keluar" (meski di versi ini belum diimplementasikan).

---

## Koneksi ke File Lain

| File                  | Hubungan                                                           |
| --------------------- | ------------------------------------------------------------------ |
| `config/koneksi.php`  | Di-`require` di baris 7, memulai sesi                              |
| `includes/navbar.php` | Link "Keluar" di navbar mengarah ke file ini (baris 46 navbar.php) |
| `auth/login.php`      | Tujuan redirect setelah logout (baris 30)                          |
