# Walkthrough Implementasi Sistem Informasi Keamanan Desa

Dokumen ini menjelaskan hasil akhir dari implementasi **Sistem Informasi Keamanan Desa (SIKD)** dengan arsitektur, database, rute, dan pengujian.

---

## 1. Arsitektur Database & Model
Sistem ini menggunakan SQLite sebagai basis data dengan skema ter-normalisasi yang mencakup seluruh tabel wajib:
*   **`roles` & `users`**: Mendukung otentikasi multi-role (Warga, Perangkat, Satpam, Kades).
*   **`reports`**: Tempat warga melaporkan kejadian keamanan.
*   **`incidents`**: Kejadian keamanan yang divalidasi dan disalin dari laporan oleh perangkat desa.
*   **`handling_records`**: Melacak log penanganan taktis di lapangan oleh petugas (Satpam).
*   **`patrol_schedules` & `patrol_logs`**: Menjadwalkan rute ronda dan mencatat laporan pos cek poin.
*   **`attachments`**: Menyimpan bukti foto pelaporan (polymorphic relationship).
*   **`notifications`**: Memberikan pemberitahuan sistem kepada pengguna secara real-time.
*   **`activity_logs`**: Mengaudit semua aktivitas kritis (login, verifikasi, penugasan).

---

## 2. Fitur Portal & Tampilan Antarmuka (Blade + TailwindCSS 4)

### A. Portal Warga (`/warga`)
*   **Dashboard**: Statistik laporan personal, status terbaru (*Baru, Diverifikasi, Diproses, Ditangani, Selesai*), dan riwayat laporan.
*   **Buat Laporan**: Form interaktif untuk mengirim aduan lengkap dengan upload gambar bukti kejadian.
*   **Panic Button**: Tombol darurat yang langsung mengirim notifikasi instan ke perangkat desa.

### B. Portal Perangkat Desa (`/perangkat`)
*   **Verifikasi Laporan**: Memverifikasi aduan warga dan mengkonversinya menjadi berkas kejadian resmi.
*   **Penugasan Satpam**: Menunjuk petugas patroli tertentu untuk menangani kejadian yang diverifikasi.
*   **Jadwal Patroli**: Manajemen penjadwalan shift ronda lapangan (Pagi, Siang, Malam) beserta wilayah cakupan.
*   **Manajemen Pengguna**: Pendaftaran dan pembaharuan akun warga, petugas, maupun kades.

### C. Portal Satpam (`/satpam`)
*   **Jadwal & Log**: Menampilkan daftar shift patroli aktif dengan modal pop-up untuk mengirimkan laporan pos ronda beserta bukti foto.
*   **Penanganan Kejadian**: Formulir penginputan laporan perkembangan tindakan lapangan dan merubah status kejadian.

### D. Portal Kepala Desa (`/kades`)
*   **Oversight Dashboard**: Menampilkan grafik tren bulanan, diagram keparahan/urgensi kasus, dan visualisasi pemetaan zona rawan (hotspots).
*   **Rekapitulasi Cetak**: Fitur penyaringan laporan berdasarkan rentang tanggal dan status dengan lembar cetak khusus (`@media print`) yang bersih dari navigasi web untuk kebutuhan PDF/cetak fisik.
*   **Audit Log**: Pelacakan riwayat aktivitas audit trail secara mendetail.

---

## 3. Pengujian Sistem (PHPUnit 11)
Telah dibuat test suite lengkap untuk memverifikasi fungsionalitas sistem secara otomatis dari hulu ke hilir:
*   **`Tests\Feature\ExampleTest`**: Memverifikasi keterjangkauan halaman utama sistem.
*   **`Tests\Feature\SecuritySystemTest`**: Melakukan pengujian alur kerja end-to-end:
    1. Registrasi warga baru.
    2. Pembuatan laporan aduan.
    3. Verifikasi laporan menjadi kejadian oleh perangkat desa.
    4. Penugasan petugas satpam.
    5. Tindak lanjut penanganan di lapangan oleh satpam.
    6. Pembuatan log ronda pos cek poin.
    7. Pemantauan statistik dan laporan rekap oleh kepala desa.

---

## 4. Cara Menjalankan Aplikasi & Pengujian

### A. Migrasi & Seeding Data
Gunakan perintah berikut untuk membangun database dan mengisi akun simulasi:
```bash
php artisan migrate:fresh --seed
```

**Akun Pengujian Default:**
*   Warga: `warga@desa.id` / `password`
*   Perangkat: `perangkat@desa.id` / `password`
*   Satpam: `satpam@desa.id` / `password`
*   Kades: `kades@desa.id` / `password`

### B. Menjalankan Server Lokal
```bash
php artisan serve
```

### C. Menjalankan Test Suite
```bash
php artisan test
```
