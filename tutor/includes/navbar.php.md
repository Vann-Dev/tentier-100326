# Catatan: `tutor/includes/navbar.php`

> **Peran file ini:** Menampilkan navigasi atas (navbar) yang muncul di semua halaman dashboard setelah pengguna login. Berisi nama aplikasi, link menu, dan tombol keluar.

---

## Konsep: Mengapa Dipisah?

Navbar dipakai di tiga halaman (`dashboard/index.php`, `dashboard/tambah.php`, `dashboard/edit.php`). Dengan memisahkannya ke file sendiri, jika ada perubahan menu, cukup ubah di satu tempat.

---

## Struktur Visual Navbar

```
┌─────────────────────────────────────────────────────────────────┐
│  🎓 Sistem Mahasiswa  │  Dashboard   Tambah Mahasiswa  │  👤 Nama ▼ │
└─────────────────────────────────────────────────────────────────┘
                                                           │
                                                     ┌─────┴─────┐
                                                     │  Keluar   │
                                                     └───────────┘
```

---

## Penjelasan Per Baris

### Baris 8–9 — Tag nav dan kontainer

```html
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid"></div>
</nav>
```

Semua kelas ini adalah kelas Bootstrap:

| Kelas              | Fungsi                                                                        |
| ------------------ | ----------------------------------------------------------------------------- |
| `navbar`           | Mengidentifikasi elemen sebagai navbar Bootstrap                              |
| `navbar-expand-lg` | Di layar lebar (≥992px) menampilkan semua menu; di layar kecil menjadi kolaps |
| `navbar-dark`      | Teks/ikon warna terang (cocok untuk latar gelap)                              |
| `bg-primary`       | Latar belakang biru (warna primary Bootstrap)                                 |
| `shadow-sm`        | Bayangan tipis di bawah navbar                                                |
| `container-fluid`  | Lebar penuh layar                                                             |

---

### Baris 11–13 — Brand / nama aplikasi

```html
<a class="navbar-brand fw-bold" href="/tutor/dashboard/">
  <i class="bi bi-mortarboard-fill me-2"></i>Sistem Mahasiswa
</a>
```

`navbar-brand` adalah kelas Bootstrap untuk logo/nama aplikasi. Selalu mengarah ke halaman utama dashboard. `bi bi-mortarboard-fill` adalah ikon topi wisuda dari Bootstrap Icons. `me-2` = margin kanan 2 unit.

---

### Baris 16–18 — Tombol hamburger (layar kecil)

```html
<button
  class="navbar-toggler"
  type="button"
  data-bs-toggle="collapse"
  data-bs-target="#navbarUtama"
>
  <span class="navbar-toggler-icon"></span>
</button>
```

Di layar kecil (ponsel), menu tidak muat ditampilkan sejajar. Bootstrap menyembunyikannya dan menampilkan tombol tiga garis (☰). Saat diklik, `data-bs-toggle="collapse"` dan `data-bs-target="#navbarUtama"` memberitahu Bootstrap untuk menampilkan/menyembunyikan elemen dengan `id="navbarUtama"`.

---

### Baris 22–34 — Menu navigasi kiri

```html
<div class="collapse navbar-collapse" id="navbarUtama">
  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
      <a class="nav-link" href="/tutor/dashboard/">
        <i class="bi bi-speedometer2 me-1"></i>Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/tutor/dashboard/tambah.php">
        <i class="bi bi-person-plus me-1"></i>Tambah Mahasiswa
      </a>
    </li>
  </ul>
</div>
```

`me-auto` pada `navbar-nav` mendorong menu selanjutnya ke kanan (karena Bootstrap menggunakan flexbox). Struktur `ul > li > a` adalah pola standar untuk daftar menu.

---

### Baris 37–51 — Menu kanan: info pengguna & logout

```html
<ul class="navbar-nav ms-auto">
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
      <i class="bi bi-person-circle me-1"></i>
      <?= htmlspecialchars($_SESSION['nama_pengguna'] ?? 'Pengguna') ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <a class="dropdown-item text-danger" href="/tutor/auth/logout.php">
          <i class="bi bi-box-arrow-right me-1"></i>Keluar
        </a>
      </li>
    </ul>
  </li>
</ul>
```

Ini adalah dropdown Bootstrap. Saat diklik, muncul menu kecil di bawahnya.

| Baris penting                   | Penjelasan                                                                                           |
| ------------------------------- | ---------------------------------------------------------------------------------------------------- |
| `$_SESSION['nama_pengguna']`    | Menampilkan nama pengguna yang sedang login (diisi saat login berhasil di `auth/login.php` baris 42) |
| `htmlspecialchars(...)`         | Mencegah XSS jika nama pengguna mengandung karakter HTML                                             |
| `?? 'Pengguna'`                 | Nilai default jika variabel sesi tidak ada                                                           |
| `dropdown-menu-end`             | Dropdown muncul dari sisi kanan (tidak terpotong di tepi layar)                                      |
| `href="/tutor/auth/logout.php"` | Link ke file logout                                                                                  |

---

## Koneksi ke File Lain

| File                   | Hubungan                                                                                         |
| ---------------------- | ------------------------------------------------------------------------------------------------ |
| `auth/login.php`       | Mengisi `$_SESSION['nama_pengguna']` (baris 42) yang ditampilkan di navbar (baris 42 navbar.php) |
| `auth/logout.php`      | Tujuan link "Keluar" (baris 46)                                                                  |
| `dashboard/index.php`  | Meng-include file ini di baris 71, link "Dashboard" di menu mengarah ke sini                     |
| `dashboard/tambah.php` | Link "Tambah Mahasiswa" di menu mengarah ke sini; file ini juga men-include navbar.php           |
| `dashboard/edit.php`   | Juga meng-include navbar.php                                                                     |
