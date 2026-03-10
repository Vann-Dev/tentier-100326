# Catatan: `tutor/dashboard/hapus.php`

> **Peran file ini:** Menghapus satu data mahasiswa dari database berdasarkan ID yang dikirim lewat URL. File ini **tidak menampilkan halaman apapun**—hanya logika PHP murni, lalu redirect.

---

## Alur Singkat

```
Pengguna klik tombol Hapus (setelah konfirmasi JavaScript)
URL: /tutor/dashboard/hapus.php?id=5
        │
        ├── Belum login? ──► Redirect ke login
        │
        ├── ID tidak valid (≤ 0)? ──► Redirect ke dashboard
        │
        └── ID valid:
                │
                ├── Cek: ada data dengan ID ini?
                │
                ├── Tidak ada ──► Redirect ke dashboard
                │
                └── Ada:
                        └── DELETE dari database
                            Redirect ke dashboard?notif=hapus
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

Pola proteksi standar—sama di semua file dashboard.

---

### Baris 16 — Ambil dan validasi ID dari URL

```php
$id_mahasiswa = intval($_GET['id'] ?? 0);
```

Sama seperti `edit.php`—`intval()` memastikan ID adalah bilangan bulat. Ini mencegah SQL Injection dan input tidak valid.

---

### Baris 18 — Hanya proses jika ID positif

```php
if ($id_mahasiswa > 0) {
```

Seluruh logika hapus dibungkus dalam kondisi ini. Jika ID `0` atau negatif, eksekusi melompat ke baris 40 (redirect ke dashboard).

---

### Baris 20–23 — Verifikasi data ada sebelum hapus

```php
$cek = $koneksi->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE id_mahasiswa = ?");
$cek->bind_param('i', $id_mahasiswa);
$cek->execute();
$cek->store_result();
```

Langkah penting: kita **cek dulu** apakah data dengan ID tersebut benar-benar ada. Ini mencegah error jika seseorang mengetik URL dengan ID yang tidak ada.

---

### Baris 25–30 — Jalankan penghapusan

```php
if ($cek->num_rows > 0) {
    $stmt = $koneksi->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt->bind_param('i', $id_mahasiswa);
    $stmt->execute();
    $stmt->close();
```

`DELETE FROM mahasiswa WHERE id_mahasiswa = ?` menghapus tepat satu baris. Klausa `WHERE` **wajib ada**—tanpanya, `DELETE FROM mahasiswa` akan menghapus **seluruh isi tabel**.

---

### Baris 33–34 — Redirect setelah berhasil

```php
header('Location: /tutor/dashboard/?notif=hapus');
exit;
```

Redirect ke dashboard dengan parameter notifikasi. Di `dashboard/index.php` (baris 86–90), parameter `hapus` memunculkan alert kuning "Data berhasil dihapus."

---

### Baris 40–41 — Fallback: redirect jika gagal

```php
header('Location: /tutor/dashboard/');
exit;
```

Jika ID tidak valid atau data tidak ditemukan, tetap redirect ke dashboard tanpa parameter notifikasi. Halaman dashboard akan tampil normal tanpa pesan apapun.

---

## Catatan Penting tentang Konfirmasi

Di `dashboard/index.php` baris 264, tombol hapus memiliki:

```js
onclick = "return confirm('Yakin ingin menghapus data ...?')";
```

Ini adalah dialog konfirmasi browser (`confirm()`). Jika pengguna klik "Cancel", fungsi mengembalikan `false` dan link tidak akan diikuti—sehingga `hapus.php` tidak pernah dipanggil. Ini lapisan perlindungan dari sisi browser (client-side), bukan sisi server.

---

## Koneksi ke File Lain

| File                           | Hubungan                                                                      |
| ------------------------------ | ----------------------------------------------------------------------------- |
| `config/koneksi.php`           | Sumber `$koneksi` dan `$_SESSION`                                             |
| `dashboard/index.php`          | Sumber link tombol Hapus (baris 261 di index.php); tujuan redirect (baris 33) |
| **Tabel database** `mahasiswa` | `DELETE` di baris 27 menghapus satu baris                                     |
