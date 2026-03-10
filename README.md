> [!NOTE]
> Download XAMPP dulu dari https://www.apachefriends.org/download.html kalo belum ada XAMPP

## Masuk kebagian explorer untuk masuk ke folder xampp

![alt text](images/explorer.png)

## Buka folder `htdocs` dan copy folder `tutor` ke dalam folder `htdocs`

![alt text](images/htdocs.png)

## Nyalakan XAMPP dan start Apache

![alt text](images/apache.png)

## Masuk ke admin phpmyadmin

![alt text](images/phpmysql.png)

## Buat database baru dengan nama `sistem_mahasiswa`

![alt text](images/sis-mhs.png)

## Buat table dengan nama `pengguna` dengan jumlah kolom 5

![alt text](images/pengguna.png)

## Buat kolom sebagai berikut:

![alt text](images/kolom.png)

```sql
id_pengguna     INT(11) AUTO_INCREMENT PRIMARY KEY,
nama_pengguna   VARCHAR(100) NOT NULL,
email           VARCHAR(150) NOT NULL UNIQUE,
kata_sandi      VARCHAR(255) NOT NULL,
dibuat_pada     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

> [!IMPORTANT]
> Ketika mengubah index menjadi unique, akan muncul tampilan ini, cukup tekan `Go`
> ![alt text](images/unq.png)

## Buat table dengan nama `mahasiswa` dengan jumlah kolom 12

![alt text](images/mahasiswa.png)

## Buat kolom sebagai berikut:

![alt text](images/mahasiswa_kolom.png)

```sql
id_mahasiswa    INT(11) AUTO_INCREMENT PRIMARY KEY,
nim             VARCHAR(20) NOT NULL UNIQUE,  -- Nomor Induk Mahasiswa
nama_lengkap    VARCHAR(150) NOT NULL,
jurusan         VARCHAR(100) NOT NULL,
program_studi   VARCHAR(100) NOT NULL,
angkatan        YEAR NOT NULL,
jenis_kelamin   ENUM('Laki-laki', 'Perempuan') NOT NULL,
email           VARCHAR(150),
nomor_telepon   VARCHAR(20),
alamat          TEXT,
dibuat_pada     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATECURRENT_TIMESTAMP
```

> [!IMPORTANT]
> Saat menambahkan enum untuk kolom `jenis_kelamin`, pastikan untuk menambahkan nilai enum `Laki-laki` dan `Perempuan`, untuk membuka menunya, tekan

![alt text](images/enumset.png)

![alt text](images/jenis_kelamin.png)

## Jadi kolom kolom nya begini

### Kolom mahasiswa

![alt text](images/kolom-mhs.png)

### Kolom pengguna

![alt text](images/kolom-pengguna.png)

## Kembali ke folder `tutor` dan tekan pada bagian ini

![alt text](images/open-folder-1.png)

> [!CAUTION]
> Tekan pada bagian kosong, jangan pada tulisan
> ![alt text](images/kosong.png)

## Ketik `cmd` lalu tekan enter

## Command prompt akan terbuka lalu ketik `code .` lalu tekan enter untuk membuka folder `tutor` di Visual Studio Code

![alt text](images/cmd.png)

## Oh ya, untuk membuka website nya, ketik `localhost/tutor/index.php` di browser

# Lanjut ke file `index.php` ya, nanti akan ada gambar di setiap folder, untuk ngejelasin gimana cara kerja nya

![alt text](images/index.png)
