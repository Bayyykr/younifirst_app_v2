# Deploy Laravel ke Railway

Project ini sudah disiapkan untuk Railway menggunakan `Dockerfile`.

## 1. Push project ke GitHub

Pastikan file berikut ikut ter-commit:

- `Dockerfile`
- `.dockerignore`
- `railway.toml`
- `docker/railway-start.sh`

## 2. Buat project Railway

1. Buka <https://railway.app>
2. `New Project` → `Deploy from GitHub repo`
3. Pilih repository project ini
4. Railway akan otomatis memakai `Dockerfile`

## 3. Tambahkan database

Di Railway project:

1. Klik `New`
2. Pilih `Database` → `MySQL`
3. Buka service aplikasi Laravel
4. Tambahkan variable database berikut:

```env
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

Jika nama service database bukan `MySQL`, sesuaikan prefix-nya dengan nama service di Railway.

## 4. Tambahkan environment variable Laravel

Minimal variable yang perlu diisi di service aplikasi:

```env
APP_NAME=YouniFirst
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:ISI_DENGAN_APP_KEY
APP_URL=https://domain-railway-kamu.up.railway.app
LOG_CHANNEL=stderr
RUN_MIGRATIONS=true
```

Buat `APP_KEY` dari lokal:

```bash
php artisan key:generate --show
```

Setelah Railway memberi domain production, update `APP_URL` sesuai domain tersebut.

## 5. Firebase

Project ini memakai Firebase Admin SDK. Karena file `storage/firebase/service-account.json` tidak boleh di-commit, gunakan Railway variable:

```env
FIREBASE_CREDENTIALS_JSON={...isi JSON service account Firebase...}
FIREBASE_PROJECT_ID=project-id-kamu
FIREBASE_API_KEY=api-key-kamu
FIREBASE_DATABASE_URL=https://project-id-kamu-default-rtdb.asia-southeast1.firebasedatabase.app
```

`docker/railway-start.sh` akan membuat file service account sementara dari `FIREBASE_CREDENTIALS_JSON` saat container start.

## 6. Deploy

Setelah semua variable diisi, klik `Deploy` / `Redeploy` di Railway.

Saat startup, container akan menjalankan:

1. `php artisan storage:link --force`
2. `php artisan migrate --force` jika `RUN_MIGRATIONS=true`
3. `php artisan config:cache`
4. `php artisan view:cache`
5. Apache dengan document root ke folder `public`

## Catatan penting

- Storage lokal Railway bersifat ephemeral. Upload file di `storage/app/public` bisa hilang saat redeploy/restart. Untuk production serius, gunakan S3/R2 atau storage eksternal.
- Jangan commit `.env` atau file Firebase service account.
- Jika migrasi tidak ingin otomatis berjalan saat startup, set `RUN_MIGRATIONS=false` lalu jalankan migrasi manual dari Railway shell.
