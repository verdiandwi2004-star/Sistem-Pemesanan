# BarokahCoffee - Sistem Pemesanan Kopi

Website pemesanan kopi berbasis PHP Native dengan 4 role user: Admin, Kasir, Dapur, dan Pelanggan.

## 🚀 Fitur Utama

### 1. **Manajemen User (4 Role)**
- **Admin**: Akses penuh ke semua halaman
- **Kasir**: Akses halaman order dan home
- **Dapur**: Akses halaman dapur dan home
- **Pelanggan**: Akses halaman order, pesanan saya, dan home

### 2. **8 Halaman Utama**
1. **Home/Dashboard**: Statistik dan overview
2. **Daftar Menu**: Manajemen menu (Admin only)
3. **Kategori Menu**: Manajemen kategori (Admin only)
4. **Order**: Pemesanan untuk kasir dan pelanggan
5. **Dapur**: Kelola pesanan masuk
6. **Order Pelanggan**: Tracking status pesanan
7. **User**: Manajemen pengguna (Admin only)
8. **Report**: Laporan dan analytics dengan export Excel (Admin only)

### 3. **Alur Pesanan**
1. Pelanggan/Kasir membuat pesanan
2. Pesanan masuk ke dapur dengan status "Pending"
3. Dapur menerima pesanan (status "Diterima")
4. Dapur menyelesaikan pesanan (status "Selesai")
5. Status realtime ditampilkan di halaman pelanggan
6. Admin/Kasir memproses pembayaran

### 4. **Report & Analytics**
- Report semua pesanan
- Report harian
- Report mingguan (7 hari terakhir)
- Report bulanan
- Report dengan range tanggal custom
- Export ke Excel (format .xls)
- Menu terpopuler
- Detail items per pesanan

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🔧 Instalasi

### 1. Setup Database

```sql
-- Buat database baru
CREATE DATABASE barokahcoffee;

-- Import file database.sql
-- Atau copy paste isi file database.sql ke phpMyAdmin
```

### 2. Konfigurasi Database

Edit file `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Sesuaikan username database
define('DB_PASS', '');              // Sesuaikan password database
define('DB_NAME', 'barokahcoffee');
```

### 3. Upload File

Upload semua file ke direktori web server:
- XAMPP: `C:\xampp\htdocs\barokahcoffee\`
- WAMP: `C:\wamp\www\barokahcoffee\`
- Linux: `/var/www/html/barokahcoffee/`

### 4. Set Permissions (Linux)

```bash
chmod -R 755 /var/www/html/barokahcoffee/
chmod -R 777 /var/www/html/barokahcoffee/assets/
```

### 5. Akses Website

Buka browser dan akses:
```
http://localhost/barokahcoffee/
```

## 👥 Akun Default

### Admin
- Username: `admin`
- Password: `admin123`
- Akses: Semua halaman

### Kasir
- Username: `kasir`
- Password: `kasir123`
- Akses: Home, Order

### Dapur
- Username: `dapur`
- Password: `dapur123`
- Akses: Home, Dapur

### Pelanggan
- Username: `customer`
- Password: `customer123`
- Akses: Home, Order, Pesanan Saya

## 📁 Struktur File

```
barokahcoffee/
├── assets/
│   ├── css/
│   │   └── style.css          # Styling utama
│   ├── js/
│   │   └── script.js          # JavaScript functions
│   └── images/                # Folder untuk gambar
├── includes/
│   ├── config.php             # Konfigurasi database
│   ├── header.php             # Header template
│   └── footer.php             # Footer template
├── index.php                  # Halaman login
├── home.php                   # Dashboard
├── daftar_menu.php           # Manajemen menu
├── kategori_menu.php         # Manajemen kategori
├── order.php                 # Pemesanan
├── dapur.php                 # Kelola pesanan dapur
├── order_pelanggan.php       # Status pesanan pelanggan
├── user.php                  # Manajemen user
├── report.php                # Report & analytics
├── export_excel.php          # Export Excel
├── logout.php                # Logout
└── database.sql              # SQL schema
```

## 🎨 Desain & Styling

### Warna Tema
- **Primary**: #143668ff (Coffee Brown)
- **Secondary**: #265090 (Blue)
- **Background**: #F5F5DC (Light Beige)
- **Success**: #28a745
- **Warning**: #ffc107
- **Danger**: #dc3545

### Font
- **PT Sans** - Google Fonts
- Fallback: Arial, sans-serif

## 💡 Cara Penggunaan

### Untuk Admin

1. **Login** sebagai admin
2. **Kelola Kategori Menu**:
   - Tambah kategori baru (Hot Coffee, Cold Coffee, dll)
   - Edit atau hapus kategori
3. **Kelola Menu**:
   - Tambah menu baru dengan kategori, harga, icon
   - Edit atau hapus menu
   - Ubah status menu (Tersedia/Habis)
4. **Kelola User**:
   - Tambah user baru (kasir, dapur, pelanggan)
   - Edit atau hapus user
5. **Lihat Report**:
   - Pilih tipe report (harian, mingguan, bulanan)
   - Download Excel
   - Lihat menu terpopuler

### Untuk Kasir

1. **Login** sebagai kasir
2. **Buat Pesanan**:
   - Pilih menu dari daftar
   - Tambah ke keranjang
   - Input data pelanggan
   - Submit pesanan
3. **Proses Pembayaran** (via halaman Order Pelanggan jika role admin)

### Untuk Dapur

1. **Login** sebagai dapur
2. **Terima Pesanan**:
   - Lihat pesanan pending
   - Klik "Terima Pesanan"
3. **Proses Pesanan**:
   - Masak pesanan
   - Klik "Tandai Selesai"
4. **Auto Refresh**: Halaman akan refresh otomatis setiap 30 detik

### Untuk Pelanggan

1. **Login** sebagai pelanggan
2. **Pesan Menu**:
   - Browse menu
   - Tambah ke keranjang
   - Checkout
3. **Track Pesanan**:
   - Lihat status realtime
   - Timeline: Pending → Diterima → Selesai
   - Lihat detail items

## 📊 Report Features

### Tipe Report
1. **Semua Pesanan**: Seluruh data pesanan
2. **Harian**: Pesanan hari tertentu
3. **Mingguan**: 7 hari terakhir
4. **Bulanan**: Bulan berjalan
5. **Range**: Custom tanggal

### Export Excel
- Format: .xls (Microsoft Excel)
- Encoding: UTF-8 with BOM
- Include:
  - Summary statistik
  - Detail pesanan
  - Menu terpopuler
  - Detail items per pesanan

## 🔒 Keamanan

- Password di-hash menggunakan MD5
- Input validation & sanitization
- SQL Injection prevention
- Session management
- Role-based access control

## 🐛 Troubleshooting

### Database Connection Error
```
Solution: Periksa config.php, pastikan username, password, dan nama database benar
```

### CSS/JS Not Loading
```
Solution: Periksa path file di includes/header.php dan footer.php
```

### Permission Denied (Linux)
```bash
sudo chmod -R 755 /var/www/html/barokahcoffee/
```

### Export Excel UTF-8 Issue
```
File sudah include UTF-8 BOM (\xEF\xBB\xBF) untuk proper encoding
```

## 📱 Responsive Design

Website ini responsive dan dapat diakses dari:
- Desktop
- Tablet
- Mobile Phone

## 🚀 Future Enhancement

Fitur yang bisa ditambahkan:
- [ ] Upload gambar menu
- [ ] Notifikasi realtime (WebSocket/Pusher)
- [ ] Print struk pesanan
- [ ] QR Code untuk meja
- [ ] Integrasi payment gateway
- [ ] Multi-language support
- [ ] Dark mode
- [ ] PWA (Progressive Web App)

## 📞 Support

Untuk pertanyaan atau bantuan, silakan hubungi developer.

## 📄 License

Free to use for educational purposes.

---

**BarokahCoffee** - Sistem Pemesanan Kopi Modern & Efisien ☕
Dibuat dengan ❤️ menggunakan PHP Native
