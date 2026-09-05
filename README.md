# Aplikasi To-Do List

## Deskripsi

Aplikasi To-Do List sederhana berbasis PHP dengan pemrograman prosedural dan `$_SESSION` sebagai penyimpanan data sementara. Tampilan dibuat responsif menggunakan Tailwind CSS melalui CDN, dengan palet warna biru dongker dan orange.

Data tugas tersimpan selama session browser aktif. Data dapat kembali ke kondisi awal setelah session berakhir atau session dihapus.

## Fitur

- Menambahkan tugas baru.
- Validasi input: tombol **Tambah** disabled jika input kosong atau hanya berisi spasi.
- Menandai tugas sebagai selesai atau belum selesai.
- Menampilkan judul tugas selesai dengan coretan.
- Memilih satu tugas atau memilih semua tugas menggunakan checkbox.
- Membatalkan semua pilihan dengan tombol **Batal pilih**.
- Menyelesaikan beberapa tugas sekaligus melalui **Selesaikan terpilih**.
- Menghapus satu tugas melalui tombol **Hapus**.
- Menghapus beberapa tugas sekaligus melalui **Hapus terpilih**.
- Menampilkan jumlah tugas belum selesai, selesai, dan total tugas.
- URL aplikasi menggunakan path `/todolist`.

## Struktur Folder

```text
.
├── index.php    # Logika PHP, tampilan HTML, dan JavaScript aplikasi
└── README.md    # Dokumentasi proyek
```

Aplikasi menggunakan konsep single-file sehingga tidak memerlukan folder asset tambahan. Tailwind CSS dimuat dari CDN.

## Prasyarat

- PHP 7.4 atau lebih baru.
- Browser modern seperti Chrome, Firefox, Edge, atau Safari.
- Koneksi internet untuk memuat Tailwind CSS dari CDN.

## Cara Menjalankan dengan PHP Built-in Server

Cara ini cocok untuk environment seperti terminal VS Code.

1. Buka terminal pada folder proyek:

	```bash
	cd /Users/entrustinv154/Office/lsp
	```

2. Jalankan server menggunakan `index.php` sebagai router:

	```bash
	php -S localhost:8000 index.php
	```

3. Buka URL berikut di browser:

	```text
	http://localhost:8000/todolist
	```

4. Tekan `Ctrl+C` atau `Cmd+C` di terminal untuk menghentikan server.

> Jangan menjalankan `php -S localhost:8000` tanpa `index.php` jika ingin menggunakan route `/todolist`.

## Cara Menjalankan dengan XAMPP

1. Pastikan Apache pada XAMPP sudah berjalan.

2. Salin folder proyek ke document root XAMPP:

	**macOS**

	```text
	/Applications/XAMPP/htdocs/todolist
	```

	**Windows**

	```text
	C:\xampp\htdocs\todolist
	```

3. Pastikan file berada di:

	```text
	/Applications/XAMPP/htdocs/todolist/index.php
	```

	atau pada Windows:

	```text
	C:\xampp\htdocs\todolist\index.php
	```

4. Buka URL berikut:

	```text
	http://localhost/todolist
	```

5. Jika Apache menggunakan port selain `80`, tambahkan port tersebut, misalnya:

	```text
	http://localhost:8080/todolist
	```

## Catatan Routing

`index.php` mengarahkan akses langsung ke `/index.php` menuju `/todolist`. Karena itu, folder aplikasi sebaiknya diberi nama `todolist` dan diletakkan langsung di document root XAMPP (`htdocs`).

Untuk PHP built-in server, `index.php` harus diberikan sebagai router:

```bash
php -S localhost:8000 index.php
```

## Kontributor

Raka Mahardika