# Dokumen Askarya

Backoffice dokumen berbasis Laravel + Vue.js untuk alur administrasi yang sebelumnya ada di `surat-app`.

## Stack

- Backend: Laravel
- Frontend: Vue.js via Inertia
- Auth: Breeze

## Alur Modul

- Penawaran
- Purchasing Order
- Invoice
- Surat Jalan
- Berita Acara
- Faktur Pajak
- Nota Toko
- Customers
- Mitra
- Users
- Document Templates
- Simulasi Pembiayaan

## Jalankan Lokal

```bash
composer install
npm install
php artisan serve
npm run dev
```

## Akun Awal

- Email: `admin@askarya.test`
- Password: `password`

## Catatan

Repo ini sengaja disiapkan sebagai fondasi bersih. Route dan halaman awal sudah disusun mengikuti alur lama, lalu implementasi data dan CRUD bisa diisi bertahap tanpa membawa struktur campur-aduk dari repo sebelumnya.
