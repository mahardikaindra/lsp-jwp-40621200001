# Aplikasi To-Do List

## Deskripsi

Aplikasi sederhana berbasis PHP untuk mencatat tugas harian. Aplikasi ini dibangun dengan mengimplementasikan prinsip pemrograman prosedural terstruktur, memanfaatkan $_SESSION untuk manajemen data (state management) sementara, dan framework Bootstrap 5 untuk tampilan antarmuka yang responsif dan estetis.

## Fitur

* Tambah tugas: Menambahkan catatan tugas baru ke dalam daftar.

* Tandai tugas selesai: Menyelesaikan tugas melalui interaksi fitur checkbox.

* Hapus tugas: Menghapus tugas yang sudah tidak relevan dari daftar.

# #Struktur Folder

index.php - Halaman utama dan core logika aplikasi (HTML, CSS, PHP).

(Karena aplikasi ini dikemas secara efisien dalam konsep single-file logic-view, maka semua script digabung di dalam index.php. Tidak memerlukan folder assets tambahan karena CSS/JS menggunakan CDN Bootstrap).

## Cara Menjalankan

* Pastikan PHP telah terinstal dan dapat dijalankan melalui terminal.

* Buka terminal pada direktori proyek ini.

* Jalankan server lokal dengan perintah berikut:

	```bash
	php -S localhost:8000
	```

* Buka browser (Chrome/Firefox).

* Akses URL: http://localhost:8000

## Kontributor

Raka Mahardika