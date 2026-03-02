-- Database: barokahcoffee
CREATE DATABASE IF NOT EXISTS barokahcoffee;
USE barokahcoffee;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kasir', 'dapur', 'pelanggan') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Menu
CREATE TABLE IF NOT EXISTS kategori_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Menu
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori_id INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255),
    status ENUM('tersedia', 'habis') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_menu(id) ON DELETE CASCADE
);

-- Tabel Pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20) NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status_pesanan ENUM('pending', 'diterima', 'selesai') DEFAULT 'pending',
    status_pembayaran ENUM('belum_bayar', 'sudah_bayar') DEFAULT 'belum_bayar',
    metode_pembayaran VARCHAR(50),
    tanggal_pesanan DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_diterima DATETIME NULL,
    tanggal_selesai DATETIME NULL,
    tanggal_bayar DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Detail Pesanan
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

-- Insert data default users
INSERT INTO users (username, password, nama, role) VALUES
('admin', MD5('admin123'), 'Administrator', 'admin'),
('kasir', MD5('kasir123'), 'Kasir 1', 'kasir'),
('dapur', MD5('dapur123'), 'Chef Barokah', 'dapur'),
('customer', MD5('customer123'), 'Customer', 'pelanggan');

-- Insert data kategori menu
INSERT INTO kategori_menu (nama_kategori) VALUES
('Hot Coffee'),
('Cold Coffee'),
('Non Coffee'),
('Snack'),
('Dessert');

-- Insert data menu sample
INSERT INTO menu (nama_menu, kategori_id, harga, deskripsi, gambar) VALUES
('Espresso', 1, 25000, 'Rich and bold espresso shot', '☕'),
('Cappuccino', 1, 35000, 'Espresso with steamed milk foam', '☕'),
('Latte', 1, 38000, 'Smooth espresso with steamed milk', '☕'),
('Americano', 1, 30000, 'Espresso dengan air panas', '☕'),
('Iced Coffee', 2, 30000, 'Refreshing cold brew coffee', '🧊'),
('Ice Latte', 2, 35000, 'Kopi susu dingin yang menyegarkan', '🧊'),
('Vanilla Milkshake', 3, 32000, 'Creamy vanilla milkshake', '🥤'),
('Chocolate Milkshake', 3, 32000, 'Rich chocolate milkshake', '🥤'),
('Croissant', 4, 25000, 'Buttery French pastry', '🥐'),
('Sandwich', 4, 35000, 'Fresh sandwich with vegetables', '🥪'),
('Chocolate Cake', 5, 45000, 'Rich chocolate layer cake', '🍰'),
('Tiramisu', 5, 50000, 'Classic Italian dessert', '🍰');
