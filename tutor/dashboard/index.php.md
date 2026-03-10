# Catatan: `tutor/dashboard/index.php`

> **Peran file ini:** Halaman utama setelah login. Menampilkan statistik ringkasan dan tabel daftar semua mahasiswa, lengkap dengan fitur pencarian dan filter program studi.

---

## Alur Singkat

```
Pengguna masuk ke /tutor/dashboard/
        │
        ├── Belum login? ──► Redirect ke login
        │
        └── Sudah login:
                │
                ├── Hitung statistik (total, prodi, angkatan)
                ├── Baca parameter pencarian dari URL (?cari=&prodi=)
                ├── Bangun query dinamis berdasarkan filter
                ├── Jalankan query → ambil daftar mahasiswa
                └── Tampilkan halaman HTML:
                        ├── Kartu statistik
                        ├── Form pencarian
                        └── Tabel mahasiswa
```

---

## Penjelasan Per Baris

### Baris 7 — Muat koneksi

```php
require_once '../config/koneksi.php';
```

Menyediakan `$koneksi` dan `$_SESSION`.

---

### Baris 10–13 — Cek login

```php
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: /tutor/auth/login.php');
    exit;
}
```

Tanda `!` berarti "tidak". Jika `id_pengguna` **tidak ada** di sesi → belum login → redirect ke login. Ini adalah **proteksi halaman** yang wajib ada di semua halaman dashboard.

---

### Baris 17–26 — Statistik ringkasan

```php
$total_mahasiswa = $koneksi->query("SELECT COUNT(*) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];

$total_prodi = $koneksi->query("SELECT COUNT(DISTINCT program_studi) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];

$total_angkatan = $koneksi->query("SELECT COUNT(DISTINCT angkatan) AS jumlah FROM mahasiswa")
    ->fetch_assoc()['jumlah'];
```

Tiga query dijalankan sekaligus untuk mengambil angka statistik:

| Variabel           | Query SQL                       | Hasil                                         |
| ------------------ | ------------------------------- | --------------------------------------------- |
| `$total_mahasiswa` | `COUNT(*)`                      | Jumlah total baris di tabel                   |
| `$total_prodi`     | `COUNT(DISTINCT program_studi)` | Jumlah prodi yang unik (tidak dihitung ganda) |
| `$total_angkatan`  | `COUNT(DISTINCT angkatan)`      | Jumlah tahun angkatan yang berbeda            |

`->fetch_assoc()['jumlah']` mengambil nilai dari kolom bernama `jumlah` (alias yang kita beri dengan `AS jumlah`).

---

### Baris 29–30 — Baca parameter filter dari URL

```php
$kata_cari    = trim($_GET['cari'] ?? '');
$filter_prodi = trim($_GET['prodi'] ?? '');
```

`$_GET` berisi parameter dari URL. Contoh: URL `?cari=budi&prodi=Informatika` membuat `$_GET['cari'] = 'budi'` dan `$_GET['prodi'] = 'Informatika'`. Jika tidak ada, default ke string kosong.

---

### Baris 38–53 — Membangun query dinamis

```php
$kondisi = [];
$params  = [];
$tipe    = '';

if ($kata_cari !== '') {
    $kondisi[] = "(nim LIKE ? OR nama_lengkap LIKE ? OR jurusan LIKE ?)";
    $pencarian = "%$kata_cari%";
    $params    = array_merge($params, [$pencarian, $pencarian, $pencarian]);
    $tipe     .= 'sss';
}

if ($filter_prodi !== '') {
    $kondisi[] = "program_studi = ?";
    $params[]  = $filter_prodi;
    $tipe     .= 's';
}
```

Query filter dibangun secara **dinamis** agar satu kode bisa menangani tiga kondisi: tidak ada filter, filter pencarian saja, filter prodi saja, atau keduanya.

| Konsep           | Penjelasan                                                             |
| ---------------- | ---------------------------------------------------------------------- |
| `LIKE '%...%'`   | Cari teks yang **mengandung** kata kunci (di awal, tengah, atau akhir) |
| `$kondisi[]`     | Tambahkan syarat ke array                                              |
| `$params[]`      | Tambahkan nilai placeholder `?` ke array                               |
| `$tipe .= 'sss'` | Operator `.=` menggabungkan string; `'sss'` = tiga parameter string    |

---

### Baris 55–63 — Rakit dan jalankan query

```php
$klausa_where = count($kondisi) ? 'WHERE ' . implode(' AND ', $kondisi) : '';
$sql = "SELECT * FROM mahasiswa $klausa_where ORDER BY dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
if ($params) {
    $stmt->bind_param($tipe, ...$params);
}
$stmt->execute();
$daftar_mahasiswa = $stmt->get_result();
```

`implode(' AND ', $kondisi)` menggabungkan semua syarat dengan `AND`. Operator `...` (spread) menguraikan array `$params` menjadi argumen terpisah untuk `bind_param`.

---

### Baris 66 — Baca parameter notifikasi

```php
$notif = $_GET['notif'] ?? '';
```

Setelah tambah/edit/hapus, file terkait melakukan redirect ke `?notif=tambah` / `?notif=edit` / `?notif=hapus`. Nilai ini dibaca di baris 76–91 untuk menampilkan notifikasi sukses.

---

### Baris 76–91 — Notifikasi kondisional

```php
<?php if ($notif === 'tambah'): ?>
    <div class="alert alert-success ..."> Data berhasil ditambahkan. </div>
<?php elseif ($notif === 'edit'): ?>
    ...
<?php elseif ($notif === 'hapus'): ?>
    ...
<?php endif; ?>
```

Sintaks `if ... : ... endif;` adalah alternatif dari kurung kurawal `{}` yang lebih mudah dibaca di dalam HTML.

---

### Baris 160–165 — Loop dropdown filter

```php
<?php while ($baris_prodi = $daftar_prodi->fetch_assoc()): ?>
    <option value="<?= htmlspecialchars($baris_prodi['program_studi']) ?>"
        <?= $filter_prodi === $baris_prodi['program_studi'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($baris_prodi['program_studi']) ?>
    </option>
<?php endwhile; ?>
```

`while ... fetch_assoc()` mengambil baris satu per satu sampai habis. Kondisi ternary `? 'selected' : ''` menambahkan atribut `selected` pada opsi yang sedang dipilih.

---

### Baris 207–269 — Loop tabel mahasiswa

```php
<?php $nomor = 1;
while ($mhs = $daftar_mahasiswa->fetch_assoc()): ?>
    <tr>
        <td><?= $nomor++ ?></td>
        ...
    </tr>
<?php endwhile; ?>
```

`$nomor++` menampilkan nomor urut lalu menambahnya 1 (post-increment). Setiap baris tabel memiliki tombol Edit yang mengirim `id_mahasiswa` ke `edit.php` dan tombol Hapus yang mengirim ke `hapus.php`.

---

## Koneksi ke File Lain

| File                           | Hubungan                              |
| ------------------------------ | ------------------------------------- |
| `config/koneksi.php`           | Sumber `$koneksi` dan `$_SESSION`     |
| `includes/header.php`          | Template HTML pembuka                 |
| `includes/navbar.php`          | Navigasi atas                         |
| `dashboard/tambah.php`         | Tombol "Tambah Mahasiswa" (baris 101) |
| `dashboard/edit.php`           | Tombol edit per baris (baris 255)     |
| `dashboard/hapus.php`          | Tombol hapus per baris (baris 261)    |
| **Tabel database** `mahasiswa` | Semua query membaca tabel ini         |
