# 🚀 Panduan Deployment: Armbian (Nginx + PHP 8.3-FPM Port 8082 + Cloudflare Tunnel)

Panduan lengkap untuk men-deploy aplikasi **JogjaMotor** di server mini PC / Single Board Computer (SBC) ber-OS **Armbian** (Debian/Ubuntu-based), menggunakan web server **Nginx** pada **port 8082**, **PHP 8.3-FPM**, dan diakses via domain publik HTTPS **`simple-rental.riyanris.my.id`** menggunakan **Cloudflare Tunnel (cloudflared)**.

---

## 📋 1. Ringkasan Topologi & Arsitektur

```
Internet (User)
      │  HTTPS (Port 443)
      ▼
Cloudflare Edge Network (SSL Managed, DDoS Protection)
      │  Encrypted Tunnel (Zero Trust / cloudflared)
      ▼
Mini PC / SBC (Armbian)
      │  HTTP Localhost (Port 8082)
      ▼
Nginx Web Server (:8082)
      │  FastCGI Unix Socket
      ▼
PHP 8.3-FPM + MySQL/MariaDB + Laravel App
```

* **OS**: Armbian (Linux ARM64 / ARMv7 / x86)
* **Local Nginx Port**: `8082`
* **PHP Engine**: `php8.3-fpm`
* **Public Domain**: `https://simple-rental.riyanris.my.id`
* **Reverse Proxy**: Cloudflare Tunnel (`cloudflared`)

---

## 📦 2. Persiapan Dependensi di Armbian

Login ke server Armbian via SSH sebagai user `root` atau user dengan hak akses `sudo`:

```bash
# Update repository
sudo apt update && sudo apt upgrade -y

# Install dependensi dasar
sudo apt install -y curl wget git unzip zip software-properties-common lsb-release ca-certificates

# Install Nginx
sudo apt install -y nginx

# Tambahkan repository PHP (jika menggunakan Debian/Ubuntu pada Armbian)
# Untuk Debian:
# sudo curl -sSL https://packages.sury.org/php/README.txt | sudo bash -
# Untuk Ubuntu:
# sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 dan ekstensi yang dibutuhkan Laravel
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-sqlite3 \
    php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip \
    php8.3-bcmath php8.3-intl php8.3-opcache

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js & NPM (untuk build assets Vite jika build di server)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 📂 3. Clone Repository & Setup Project

Asumsikan project ditaruh di `/var/www/sewamotor`:

```bash
# Buat direktori (jika belum ada)
sudo mkdir -p /var/www/sewamotor
sudo chown -R $USER:$USER /var/www/sewamotor

# Clone atau salin source code project ke direktori ini
git clone <URL_REPO_ANDA> /var/www/sewamotor
cd /var/www/sewamotor

# Copy file environment
cp .env.example .env

# Install dependensi PHP untuk produksi
composer install --no-dev --optimize-autoloader

# Install dependensi JS & build frontend (CSS / JS Tailwind)
npm install
npm run build

# Generate APP_KEY
php artisan key:generate
```

---

## ⚙️ 4. Konfigurasi Environment (`.env`)

Edit file `.env`:
```bash
nano .env
```

Sesuaikan parameter penting berikut:
```ini
APP_NAME=JogjaMotor
APP_ENV=production
APP_DEBUG=false
APP_URL=https://simple-rental.riyanris.my.id

# Konfigurasi Database (Sesuaikan dengan DB MariaDB/MySQL Anda)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sewamotor
DB_USERNAME=sewamotor_user
DB_PASSWORD=password_db_anda_yang_kuat

# Atau jika menggunakan SQLite:
# DB_CONNECTION=sqlite
```

Jalankan migrasi database dan buat symlink storage:
```bash
# Jalankan migrasi tabel
php artisan migrate --force

# Jalankan seeder admin/master awal (jika instalasi baru)
php artisan db:seed --force

# Buat symlink public/storage -> storage/app/public
php artisan storage:link

# Optimalkan cache Laravel untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Atur kepemilikan dan permission agar Nginx / PHP-FPM dapat membaca dan menulis:
```bash
sudo chown -R www-data:www-data /var/www/sewamotor/storage /var/www/sewamotor/bootstrap/cache
sudo chmod -R 775 /var/www/sewamotor/storage /var/www/sewamotor/bootstrap/cache
```

---

## 🌐 5. Konfigurasi Nginx (Port 8082)

Buat file konfigurasi Nginx untuk aplikasi:
```bash
sudo nano /etc/nginx/sites-available/sewamotor
```

Isi dengan konfigurasi berikut:

```nginx
server {
    listen 8082;
    listen [::]:8082;
    
    server_name simple-rental.riyanris.my.id localhost;
    root /var/www/sewamotor/public;

    # Mengizinkan upload foto KTP / bukti transfer hingga 20MB
    client_max_body_size 20M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Reverse proxy header forwarding (dari Cloudflare Tunnel)
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $http_x_forwarded_proto;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # FastCGI PHP 8.3 FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120;
    }

    # Blokir akses ke hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Caching untuk asset statis
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|svg|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Aktifkan konfigurasi dan restart Nginx:
```bash
# Aktifkan site
sudo ln -sf /etc/nginx/sites-available/sewamotor /etc/nginx/sites-enabled/

# Uji konfigurasi syntax Nginx
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

> 💡 **Tes Local**: Anda dapat menguji akses lokal via `curl -I http://localhost:8082` atau buka `http://IP_LOKAL_ARMBIAN:8082` di browser jaringan WiFi/LAN lokal Anda.

---

## ⚡ 6. Konfigurasi Opcache PHP 8.3 (Performa Cepat di Armbian)

Untuk memaksimalkan performa di SoC Armbian yang hemat daya:
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Pastikan baris berikut aktif:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

---

## ☁️ 7. Setup Cloudflare Tunnel (`cloudflared`)

Cloudflare Tunnel memungkinkan server Armbian diakses dari internet publik **tanpa perlu buka port router / port forwarding / IP Publik statis**.

### Langkah A: Install `cloudflared` di Armbian
Cek arsitektur mesin (`uname -m`). Untuk ARM64:
```bash
# Download cloudflared binary untuk ARM64
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64.deb

# Untuk mesin ARM 32-bit (armhf):
# curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm.deb

# Install package
sudo dpkg -i cloudflared.deb
```

---

### Langkah B: Hubungkan Tunnel (Pilih Cara 1 atau Cara 2)

#### CARA 1: Melalui Cloudflare Zero Trust Dashboard (Paling Mudah & Direkomendasikan)
1. Buka [Cloudflare Zero Trust Dashboard](https://one.dash.cloudflare.com/).
2. Masuk ke **Networks** > **Tunnels** > Klik **Create a Tunnel**.
3. Pilih tipe: **Cloudflared**, beri nama misalnya: `armbian-sewamotor`.
4. Pilih environment **Debian** / **ARM64**.
5. Salin perintah instalasi token yang muncul di dashboard, contoh:
   ```bash
   sudo cloudflared service install eyJhIjoi...TOKEN_ANDA...
   ```
6. Pada tab **Public Hostname**:
   - **Subdomain**: `simple-rental`
   - **Domain**: `riyanris.my.id`
   - **Path**: *(kosongkan)*
   - **Service**:
     - **Type**: `HTTP`
     - **URL**: `localhost:8082` (atau `127.0.0.1:8082`)
7. Klik **Save hostname**. Selesai!

---

#### CARA 2: Melalui CLI Server Armbian
1. Login akun Cloudflare:
   ```bash
   cloudflared tunnel login
   ```
   *(Buka tautan yang muncul di browser untuk memilih domain `riyanris.my.id`)*.

2. Buat tunnel baru:
   ```bash
   cloudflared tunnel create sewamotor-tunnel
   ```
   *(Catat UUID Tunnel yang dibuat)*.

3. Buat file konfigurasi:
   ```bash
   mkdir -p ~/.cloudflared
   nano ~/.cloudflared/config.yml
   ```
   Isi dengan:
   ```yaml
   tunnel: <UUID_TUNNEL_ANDA>
   credentials-file: /root/.cloudflared/<UUID_TUNNEL_ANDA>.json

   ingress:
     - hostname: simple-rental.riyanris.my.id
       service: http://127.0.0.1:8082
     - service: http_status:404
   ```

4. Buat routing DNS di Cloudflare:
   ```bash
   cloudflared tunnel route dns sewamotor-tunnel simple-rental.riyanris.my.id
   ```

5. Install & jalankan sebagai daemon service otomatis:
   ```bash
   sudo cloudflared service install
   sudo systemctl enable --now cloudflared
   ```

---

## 🔒 8. Verifikasi TrustProxies di Laravel (Otomatis Sudah Diterapkan)

Karena traffic melewati Cloudflare Tunnel (HTTPS) lalu diteruskan ke Nginx (HTTP Port 8082), Laravel perlu mempercayai header reverse-proxy (`X-Forwarded-Proto`, `X-Forwarded-For`).

Konfigurasi ini **sudah diaktifkan** pada `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    // ...
})
```
Hal ini menjamin:
- Asset CSS/JS Vite & route URL otomatis menggunakan format **`https://`**.
- Tidak terjadi error *Mixed Content (blocked: mixed-content)* di browser.
- IP pengunjung yang tercatat di database adalah IP asli pelanggan (bukan IP internal tunnel).

---

## 🔄 9. Perawatan & Script Update Otomatis

Jika ada pembaruan kode dari repositori Git di kemudian hari, cukup jalankan perintah:

```bash
cd /var/www/sewamotor
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
sudo systemctl restart php8.3-fpm
```

---

## ✅ 10. Pengujian Akhir
1. Buka browser: **`https://simple-rental.riyanris.my.id`**
2. Cek apakah gembok HTTPS aktif dan tampilan Tailwind CSS termuat dengan rapi.
3. Coba login ke panel admin: **`https://simple-rental.riyanris.my.id/login`**
4. Tes simulasi booking & upload gambar (foto KTP / bukti transfer) untuk memastikan permission folder storage berjalan normal.
