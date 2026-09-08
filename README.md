# SmartSplit — Student Budget Allocation

**SmartSplit** adalah aplikasi kalkulator alokasi anggaran personal berbasis web yang dirancang untuk mempermudah pengelolaan finansial secara proporsional, presisi, dan instan. Mengusung arsitektur *stateless* tanpa persistensi basis data, aplikasi ini memproses kalkulasi pembagian dana kebutuhan (*needs*), keinginan (*wants*), dan tabungan (*savings*).

## Identitas
- **Nama**: Ayu Atikah
- **NIM**: 24/545004/SV/25588
- **Program Studi**: Software Engineering
- **Mata Kuliah**: Konstruksi & Evolusi Perangkat Lunak

## Tech Stack

* **Backend**: Laravel 11 (PHP 8.3)
* **Frontend**: Tailwind CSS & Alpine.js (via CDN)
* **Testing & QA**: PHPUnit & Laravel Pint
* **CI/CD**: GitHub Actions

## Fitur Utama

- **Kalkulasi Proporsional Instan**: Menghitung pembagian nominal dana secara riil berdasarkan total alokasi dan persentase yang disesuaikan.
- **Preset Rasio Finansial Populer**: Dukungan pembagian anggaran siap pakai seperti aturan populer 50/30/20, 70/20/10, maupun rasio berimbang 40/40/20.
- **Validasi Alokasi 100% Presisi**: Mekanisme validasi ketat yang menjamin total akumulasi persentase pembagian bernilai tepat seratus persen sebelum diproses.
- **Arsitektur Stateless & Bebas Beban**: Tidak menyimpan data sensitif pengguna atau riwayat transaksi di server, menjamin privasi dan latensi komputasi minimal.


## Menjalankan Proyek Secara Lokal

1. Pasang dependensi PHP:
   ```bash
   composer install
   ```

2. Gandakan konfigurasi lingkungan dan buat kunci aplikasi:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Nyalakan server pengembangan:
   ```bash
   php artisan serve
   ```

4. Buka peramban di `http://127.0.0.1:8000`.

## Menjalankan test
php artisan test

## Alur Branching

Sistem menerapkan alur Git branch terlindungi (*branch protection*):
- **`main`**: Jalur stabil siap rilis, dilindungi (*protected*), hanya menerima penggabungan melalui Pull Request dari `dev`.
- **`dev`**: Jalur integrasi harian, dilindungi (*protected*), hanya menerima penggabungan melalui Pull Request dari cabang fitur.
- **`feature/custom-allocation`**: Cabang kerja untuk implementasi kalkulator dan penyesuaian rasio budget.

## Endpoint
POST /calculate — lihat `app/Http/Controllers/BudgetController.php`.

