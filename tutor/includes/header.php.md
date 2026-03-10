# Catatan: `tutor/includes/header.php`

> **Peran file ini:** Template HTML bagian atas (`<html>`, `<head>`, awal `<body>`) yang dipakai **bersama** oleh semua halaman. Dengan file ini, kita tidak perlu menulis tag HTML dasar berulang kali.

---

## Konsep: Mengapa File Ini Penting?

Bayangkan tanpa file ini—setiap halaman harus menulis ini sendiri:

```html
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>...</title>
    <link rel="stylesheet" href="bootstrap.css" />
    ...
  </head>
  <body></body>
</html>
```

Dengan `header.php`, cukup satu baris di file lain:

```php
<?php include '../includes/header.php'; ?>
```

Ini adalah prinsip **DRY (Don't Repeat Yourself)**.

---

## Penjelasan Per Baris

### Baris 8 — Nilai default judul

```php
$judul_halaman = $judul_halaman ?? 'Sistem Mahasiswa';
```

Operator `??` (null coalescing): jika `$judul_halaman` sudah didefinisikan di file yang memanggil (misal `$judul_halaman = 'Dashboard'`), gunakan nilai itu. Jika tidak ada, gunakan default `'Sistem Mahasiswa'`.

> Jadi setiap file yang meng-include header.php harus mendefinisikan `$judul_halaman` sebelum baris `include`.

---

### Baris 10–11 — Deklarasi dokumen HTML

```html
<!DOCTYPE html>
<html lang="id"></html>
```

`<!DOCTYPE html>` memberitahu browser ini adalah dokumen HTML5. `lang="id"` menandai bahasa halaman sebagai Indonesia—berguna untuk pembaca layar dan mesin pencari.

---

### Baris 14–15 — Meta tag wajib

```html
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
```

| Meta tag          | Fungsi                                                                                  |
| ----------------- | --------------------------------------------------------------------------------------- |
| `charset="UTF-8"` | Mendukung semua karakter (termasuk huruf Indonesia)                                     |
| `viewport`        | Membuat tampilan responsif di ponsel—tanpa ini, halaman terlihat sangat kecil di mobile |

---

### Baris 16 — Judul tab browser

```html
<title><?= htmlspecialchars($judul_halaman) ?> — Sistem Mahasiswa</title>
```

Menampilkan judul di tab browser. `<?= ... ?>` adalah shorthand `echo`. `htmlspecialchars()` mencegah karakter HTML yang tidak disengaja di judul merusak tampilan.

---

### Baris 19–24 — Load Bootstrap 5 dan Bootstrap Icons

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/.../bootstrap.min.css" />
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/.../bootstrap-icons.min.css"
/>
```

Kedua file CSS dimuat dari **CDN (Content Delivery Network)**—server pihak ketiga yang menyimpan library. Keuntungan: tidak perlu download dan simpan file Bootstrap di proyek.

| Library         | Fungsi                                                                           |
| --------------- | -------------------------------------------------------------------------------- |
| Bootstrap 5 CSS | Styling komponen (kartu, tombol, tabel, form, grid, dll.)                        |
| Bootstrap Icons | Ikon-ikon yang dipakai di seluruh aplikasi (`bi bi-person`, `bi bi-trash`, dsb.) |

---

### Baris 27 — CSS kustom

```html
<link rel="stylesheet" href="/tutor/assets/style.css" />
```

File CSS milik proyek sendiri untuk tampilan khusus yang tidak disediakan Bootstrap (misal kelas `.halaman-auth`, `.kartu-auth`). Dimuat setelah Bootstrap agar bisa menimpa styling Bootstrap jika perlu.

---

### Baris 30 — Pembuka body

```html
<body class="bg-light"></body>
```

`bg-light` adalah kelas Bootstrap yang memberi latar belakang abu-abu muda. Tag `<body>` tidak ditutup di sini—penutupnya ada di masing-masing file PHP setelah konten halaman.

---

## Koneksi ke File Lain

| File                   | Cara menggunakan                                         |
| ---------------------- | -------------------------------------------------------- |
| `auth/login.php`       | `<?php include '../includes/header.php'; ?>` (baris 60)  |
| `auth/register.php`    | `<?php include '../includes/header.php'; ?>` (baris 67)  |
| `dashboard/index.php`  | `<?php include '../includes/header.php'; ?>` (baris 70)  |
| `dashboard/tambah.php` | `<?php include '../includes/header.php'; ?>` (baris 85)  |
| `dashboard/edit.php`   | `<?php include '../includes/header.php'; ?>` (baris 117) |

> Semua file yang meng-include header ini **wajib** mendefinisikan `$judul_halaman` terlebih dahulu.
