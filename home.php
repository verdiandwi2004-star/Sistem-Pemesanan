<?php
require_once 'includes/header.php';

// Ambil statistik
$today = date('Y-m-d');

// Ambil menu promo khusus untuk pelanggan
$query_promo = "SELECT m.* FROM menu m 
                JOIN kategori_menu k ON m.kategori_id = k.id 
                WHERE k.nama_kategori = 'promo' AND m.status = 'tersedia'
                LIMIT 4";
$result_promo = mysqli_query($conn, $query_promo);

// Total pesanan hari ini
$query_today = "SELECT COUNT(*) as total FROM pesanan WHERE DATE(tanggal_pesanan) = '$today'";
$result_today = mysqli_query($conn, $query_today);
$total_today = mysqli_fetch_assoc($result_today)['total'];

// Total revenue hari ini
$query_revenue = "SELECT SUM(total_harga) as total FROM pesanan 
                  WHERE DATE(tanggal_pesanan) = '$today' AND status_pembayaran = 'sudah_bayar'";
$result_revenue = mysqli_query($conn, $query_revenue);
$total_revenue = mysqli_fetch_assoc($result_revenue)['total'] ?? 0;

// Pesanan pending
$query_pending = "SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['total'];

// Pesanan selesai hari ini
$query_complete = "SELECT COUNT(*) as total FROM pesanan 
                   WHERE DATE(tanggal_pesanan) = '$today' AND status_pesanan = 'selesai'";
$result_complete = mysqli_query($conn, $query_complete);
$total_complete = mysqli_fetch_assoc($result_complete)['total'];

// Pesanan terbaru
$query_recent = "SELECT p.*, u.nama as nama_user 
                 FROM pesanan p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 ORDER BY p.tanggal_pesanan DESC LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);
?>

<h1 class="page-title">Dashboard BarokahCoffee</h1>

<?php if ($_SESSION['role'] == 'pelanggan'): ?>
    <h2 style="margin-bottom: 1rem; color: var(--primary-color);">🔥 Promo Spesial Hari Ini</h2>
    <div class="grid grid-4">
        <?php if (mysqli_num_rows($result_promo) > 0): ?>
            <?php while ($promo = mysqli_fetch_assoc($result_promo)): ?>
                <div class="stat-card" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                    <div class="menu-image-container" style="width: 100%;">
                        <img src="assets/img/<?php echo $promo['gambar']; ?>" alt="<?php echo $promo['nama_menu']; ?>"
                             style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <div class="stat-content">
                        <h3 style="font-size: 1rem;"><?php echo $promo['nama_menu']; ?></h3>
                        <div class="stat-value" style="font-size: 1.2rem; color: var(--secondary-color);">
                            <?php echo format_rupiah($promo['harga']); ?>
                        </div>
                    </div>
                    <a href="order.php" class="btn btn-primary btn-sm"
                       style="width: 100%; justify-content: center; margin-top: 5px;">Pesan</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: span 4; text-align: center; padding: 2rem; background: #fff; border-radius: 8px;">
                Belum ada promo tersedia saat ini.
            </p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-content">
                <h3>Pesanan Hari Ini</h3>
                <div class="stat-value"><?php echo $total_today; ?></div>
            </div>
            <div class="stat-icon blue">🛒</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h3>Revenue Hari Ini</h3>
                <div class="stat-value"><?php echo format_rupiah($total_revenue); ?></div>
            </div>
            <div class="stat-icon green">💰</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h3>Pesanan Pending</h3>
                <div class="stat-value"><?php echo $total_pending; ?></div>
            </div>
            <div class="stat-icon orange">⏰</div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h3>Selesai Hari Ini</h3>
                <div class="stat-value"><?php echo $total_complete; ?></div>
            </div>
            <div class="stat-icon purple">✅</div>
        </div>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] == 'dapur'): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Informasi Dapur</h2>
        </div>
        <p style="font-size: 1.1rem;">Anda memiliki <strong><?php echo $total_pending; ?> pesanan</strong> yang perlu diproses.</p>
        <a href="dapur.php" class="btn btn-primary">Lihat Pesanan Dapur</a>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] == 'kasir'): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Informasi Kasir</h2>
        </div>
        <p style="font-size: 1.1rem;">Total pesanan hari ini: <strong><?php echo $total_today; ?> pesanan</strong></p>
        <p style="font-size: 1.1rem;">Revenue hari ini: <strong><?php echo format_rupiah($total_revenue); ?></strong></p>
        <a href="order.php" class="btn btn-primary">Buat Pesanan Baru</a>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] == 'pelanggan'): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Selamat Datang di BarokahCoffee!</h2>
        </div>
        <p style="font-size: 1.1rem; margin-bottom: 1rem;">Nikmati kopi terbaik kami dan pesan sekarang!</p>
        <div style="display: flex; gap: 1rem;">
            <a href="order.php" class="btn btn-primary">Pesan Sekarang</a>
            <a href="order_pelanggan.php" class="btn btn-info">Lihat Pesanan Saya</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>