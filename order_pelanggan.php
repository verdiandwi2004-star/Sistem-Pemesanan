<?php
require_once 'includes/header.php';

// Cek akses - pelanggan, admin, dan kasir
if (!in_array($_SESSION['role'], ['pelanggan', 'admin', 'kasir'])) {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// Proses pembayaran (untuk admin dan kasir)
if (isset($_POST['bayar']) && in_array($_SESSION['role'], ['admin', 'kasir'])) {
    $pesanan_id = clean_input($_POST['pesanan_id']);
    $metode_pembayaran = clean_input($_POST['metode_pembayaran']);
    
    $query = "UPDATE pesanan SET 
              status_pembayaran = 'sudah_bayar',
              metode_pembayaran = '$metode_pembayaran',
              tanggal_bayar = NOW()
              WHERE id = $pesanan_id";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Pembayaran berhasil diproses!';
    } else {
        $error = 'Gagal memproses pembayaran!';
    }
}

// Filter pesanan berdasarkan role
$where_clause = '';
if ($_SESSION['role'] == 'pelanggan') {
    $where_clause = "WHERE p.user_id = {$_SESSION['user_id']}";
}

// Ambil pesanan
$query = "SELECT p.*, u.nama as nama_user
          FROM pesanan p
          JOIN users u ON p.user_id = u.id
          $where_clause
          ORDER BY p.tanggal_pesanan DESC";
$result = mysqli_query($conn, $query);
?>

<h1 class="page-title">
    <?php echo $_SESSION['role'] == 'pelanggan' ? 'Pesanan Saya' : 'Order untuk Pelanggan'; ?>
</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (mysqli_num_rows($result) == 0): ?>
    <div class="card">
        <div style="text-align: center; padding: 3rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">📱</div>
            <h2 style="color: var(--primary-color); margin-bottom: 0.5rem;">Belum Ada Pesanan</h2>
            <p style="color: #666;">Belum ada pesanan yang terdaftar</p>
            <?php if ($_SESSION['role'] == 'pelanggan'): ?>
                <a href="order.php" class="btn btn-primary" style="margin-top: 1rem;">
                    🛒 Buat Pesanan
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="grid" style="grid-template-columns: 1fr;">
        <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
            <div class="order-card" style="border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: white;">
                <div class="order-header" style="display: flex; justify-content: space-between;">
                    <div style="flex: 1;">
                        <h3 style="color: var(--primary-color); margin-bottom: 0.3rem;">
                            <?php echo $pesanan['kode_pesanan']; ?>
                        </h3>
                        <p style="color: #666; font-size: 0.9rem; margin: 0.2rem 0;">
                            <strong><?php echo $pesanan['nama_pelanggan']; ?></strong>
                        </p>
                        <p style="color: #999; font-size: 0.85rem;">
                            📅 <?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])); ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-<?php echo $pesanan['status_pesanan']; ?>">
                            <?php echo ucfirst($pesanan['status_pesanan']); ?>
                        </span>
                        <br>
                        <span class="badge badge-<?php echo $pesanan['status_pembayaran'] == 'sudah_bayar' ? 'bayar' : 'belum'; ?>" 
                              style="margin-top: 0.5rem; display: inline-block;">
                            <?php echo $pesanan['status_pembayaran'] == 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar'; ?>
                        </span>
                    </div>
                </div>

                <div class="order-items" style="margin-top: 1rem; border-top: 1px solid #eee; padding-top: 1rem;">
                    <?php
                    $detail_query = "SELECT dp.*, m.nama_menu 
                                    FROM detail_pesanan dp 
                                    JOIN menu m ON dp.menu_id = m.id 
                                    WHERE dp.pesanan_id = {$pesanan['id']}";
                    $detail_result = mysqli_query($conn, $detail_query);
                    while ($detail = mysqli_fetch_assoc($detail_result)):
                    ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem; font-size: 0.9rem;">
                            <span><?php echo $detail['nama_menu']; ?> x <?php echo $detail['jumlah']; ?></span>
                            <span><?php echo format_rupiah($detail['subtotal']); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="order-total" style="text-align: right; font-weight: bold; font-size: 1.1rem; margin-top: 0.5rem; border-top: 1px solid #eee; padding-top: 0.5rem;">
                    Total: <?php echo format_rupiah($pesanan['total_harga']); ?>
                </div>

                <?php if (in_array($_SESSION['role'], ['admin', 'kasir']) && $pesanan['status_pembayaran'] == 'belum_bayar' && $pesanan['status_pesanan'] == 'selesai'): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #eee;">
                        <form method="POST" action="" style="display: flex; gap: 0.5rem;">
                            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                            <select name="metode_pembayaran" class="form-control" style="padding: 5px;" required>
                                <option value="">Pilih Metode</option>
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="ewallet">E-Wallet</option>
                            </select>
                            <button type="submit" name="bayar" class="btn btn-success">💰 Bayar</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>