# ReWortha Demo

Bandingkan estimasi biaya bulanan untuk memperbaiki atau mengganti perangkat. Dibuat dengan Laravel, Blade, dan Tailwind CSS.

Butuh PHP 8.4 64-bit dengan `pdo_sqlite` dan `pdo_pgsql`, Composer, serta Node.js 24.

Jalankan dari checkout baru.

```sh
composer setup
php -r "is_file('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
npm ci
npm run build
php artisan serve
```

Buka [demo lokal](http://127.0.0.1:8000) dan daftar akun. Swagger tersedia di `/docs`. Jalankan tes dengan `composer test`.
