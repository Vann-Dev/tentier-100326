# Catatan: `tutor/dashboard/edit.php`

> **Peran file ini:** Menampilkan form yang sudah terisi data mahasiswa yang dipilih, lalu memproses perubahan yang disimpan pengguna ke database.

---

## Alur Singkat

```
Pengguna klik tombol Edit di tabel (dari dashboard/index.php)
URL: /tutor/dashboard/edit.php?id=5
        │
        ├── Belum login? ──► Redirect ke login
        │
        ├── ID tidak valid / tidak ada? ──► Redirect ke dashboard
        │
        └── ID valid:
                │
                ├── Ambil data mahasiswa dari database berdasarkan ID
                │
                ├── Baru buka (GET) ──► Tampilkan form dengan data lama
                │
                └── Klik "Perbarui Data" (POST):
                        │
                        ├── Validasi semua input
                        ├── Cek NIM: sudah dipakai mahasiswa LAIN?
                        │
                        ├── Ada masalah ──► Tampilkan error
                        │
                        └── Semua valid:
                                └── UPDATE data di database
                                    Redirect ke dashboard?notif=edit
```

---

## Penjelasan Per Baris

### Baris 16 — Ambil ID dari URL

```php
$id_mahasiswa = intval($_GET['id'] ?? 0);
```

`$_GET['id']` membaca parameter `id` dari URL (contoh: `?id=5`). `intval()` mengubahnya ke integer dan mengembalikan `0` jika bukan angka—ini mencegah nilai aneh seperti `id=abc` atau `id=1;DROP TABLE`.

---

### Baris 19–22 — Validasi ID

```php
if ($id_mahasiswa <= 0) {
    header('Location: /tutor/dashboard/');
    exit;
}
```

ID harus angka positif. Jika `0` atau negatif (berarti tidak valid), kembali ke dashboard.

---

### Baris 25–28 — Ambil data mahasiswa dari database

```php
$stmt_cari = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt_cari->bind_param('i', $id_mahasiswa);
$stmt_cari->execute();
$hasil = $stmt_cari->get_result();
```

Query `SELECT * FROM mahasiswa WHERE id_mahasiswa = ?` mencari satu baris data berdasarkan ID. Tipe `'i'` di `bind_param` artinya integer.

---

### Baris 31–34 — Cek data ada di database

```php
if ($hasil->num_rows === 0) {
    header('Location: /tutor/dashboard/');
    exit;
}
```

Jika pengguna mengetik URL dengan ID yang tidak ada (misal `?id=99999`), kita amankan dengan redirect ke dashboard.

---

### Baris 36–37 — Simpan data ke variabel

```php
$data_mahasiswa = $hasil->fetch_assoc();
$stmt_cari->close();
```

`fetch_assoc()` mengambil baris sebagai array asosiatif. Contoh: `$data_mahasiswa['nama_lengkap']` berisi nama mahasiswa tersebut. `close()` menutup statement setelah selesai—praktik yang baik untuk pengelolaan memori.

---

### Baris 65–71 — Cek NIM: sudah dipakai orang lain?

```php
$cek_nim = $koneksi->prepare(
    "SELECT id_mahasiswa FROM mahasiswa WHERE nim = ? AND id_mahasiswa != ?"
);
$cek_nim->bind_param('si', $nim, $id_mahasiswa);
```

Ini berbeda dari `tambah.php`. Di sini kita cek apakah NIM yang diinput sudah dipakai mahasiswa **selain** yang sedang diedit. Klausa `AND id_mahasiswa != ?` mengecualikan diri sendiri—sehingga mahasiswa boleh menyimpan NIM-nya sendiri tanpa dianggap duplikat.

---

### Baris 77–96 — UPDATE data di database

```php
$stmt = $koneksi->prepare(
    "UPDATE mahasiswa SET
        nim = ?, nama_lengkap = ?, jurusan = ?, program_studi = ?,
        angkatan = ?, jenis_kelamin = ?, email = ?,
        nomor_telepon = ?, alamat = ?
     WHERE id_mahasiswa = ?"
);
$stmt->bind_param(
    'ssssissssi',
    ...
    $id_mahasiswa
);
```

`UPDATE ... SET ... WHERE` memperbarui baris yang sudah ada. Klausa `WHERE id_mahasiswa = ?` **wajib ada**—tanpanya, semua baris di tabel akan diperbarui! Tipe terakhir `i` untuk `$id_mahasiswa` (integer).

---

### Baris 110–112 — Pertahankan input baru saat ada error

```php
if ($pesan_error) {
    $data_mahasiswa = array_merge($data_mahasiswa, $_POST);
}
```

`array_merge()` menggabungkan data lama dan data baru yang diketik. Jika ada error, form menampilkan **input terbaru yang diketik pengguna** (bukan data lama dari database).

---

### Baris 156 — Form diisi dengan data yang ada

```php
<input ... value="<?= htmlspecialchars($data_mahasiswa['nim']) ?>">
```

`$data_mahasiswa` bisa berisi data dari database (saat pertama buka) atau input baru (saat ada error di POST). `htmlspecialchars()` wajib dipakai untuk mencegah XSS.

---

## Koneksi ke File Lain

| File                           | Hubungan                                                                     |
| ------------------------------ | ---------------------------------------------------------------------------- |
| `config/koneksi.php`           | Sumber `$koneksi` dan `$_SESSION`                                            |
| `includes/header.php`          | Template HTML pembuka                                                        |
| `includes/navbar.php`          | Navigasi atas                                                                |
| `dashboard/index.php`          | Sumber link tombol Edit (baris 255 di index.php); tujuan redirect (baris 99) |
| **Tabel database** `mahasiswa` | `SELECT` untuk ambil data, `UPDATE` untuk simpan perubahan                   |
