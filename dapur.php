<?php
require_once 'includes/header.php';

// Cek akses - hanya dapur dan admin
if (!in_array($_SESSION['role'], ['dapur', 'admin'])) {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// Update status pesanan
if (isset($_POST['update_status']) && isset($_POST['pesanan_id'])) {
    $pesanan_id = clean_input($_POST['pesanan_id']);
    $status = clean_input($_POST['update_status']);

    // Validasi status hanya boleh 'diterima' atau 'selesai'
    if (in_array($status, ['diterima', 'selesai'])) {
        $tanggal_field = '';
        if ($status == 'diterima') {
            $tanggal_field = ', tanggal_diterima = NOW()';
        } elseif ($status == 'selesai') {
            $tanggal_field = ', tanggal_selesai = NOW()';
        }

        $query = "UPDATE pesanan SET status_pesanan = '$status' $tanggal_field WHERE id = $pesanan_id";
        if (mysqli_query($conn, $query)) {
            $success = 'Status pesanan berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate status pesanan!';
        }
    } else {
        $error = 'Status pesanan tidak valid!';
    }
}

// Ambil pesanan yang pending dan diterima
$query = "SELECT p.*, u.nama as nama_user,
          (SELECT GROUP_CONCAT(CONCAT(m.nama_menu, ' (', dp.jumlah, 'x)') SEPARATOR ', ')
           FROM detail_pesanan dp 
           JOIN menu m ON dp.menu_id = m.id 
           WHERE dp.pesanan_id = p.id) as items
          FROM pesanan p
          JOIN users u ON p.user_id = u.id
          WHERE p.status_pesanan IN ('pending', 'diterima')
          ORDER BY p.tanggal_pesanan ASC";
$result = mysqli_query($conn, $query);
?>

<h1 class="page-title">Dapur - Kelola Pesanan</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (mysqli_num_rows($result) == 0): ?>
    <div class="card">
        <div style="text-align: center; padding: 3rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">👨‍🍳</div>
            <h2 style="color: var(--primary-color); margin-bottom: 0.5rem;">Tidak Ada Pesanan</h2>
            <p style="color: #666;">Semua pesanan sudah selesai diproses</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h3 style="color: var(--primary-color); margin-bottom: 0.3rem;">
                            <?php echo $pesanan['kode_pesanan']; ?>
                        </h3>
                        <p style="color: #666; font-size: 0.9rem; margin: 0.2rem 0;">
                            <strong><?php echo $pesanan['nama_pelanggan']; ?></strong>
                        </p>
                        <p style="color: #666; font-size: 0.85rem; margin: 0.2rem 0;">
                            📞 <?php echo $pesanan['no_telepon']; ?>
                        </p>
                        <p style="color: #999; font-size: 0.85rem; margin: 0.2rem 0;">
                            ⏰ <?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])); ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo $pesanan['status_pesanan']; ?>">
                        <?php echo ucfirst($pesanan['status_pesanan']); ?>
                    </span>
                </div>
                
                <div class="order-items">
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Detail Pesanan:</h4>
                    <?php
                    // Ambil detail pesanan
                    $detail_query = "SELECT dp.*, m.nama_menu, m.gambar 
                                    FROM detail_pesanan dp 
                                    JOIN menu m ON dp.menu_id = m.id 
                                    WHERE dp.pesanan_id = {$pesanan['id']}";
                    $detail_result = mysqli_query($conn, $detail_query);
                    
                    while ($detail = mysqli_fetch_assoc($detail_result)):
                    ?>
                        <div class="order-item">
                            <div>
                                <span class="menu-icon" ><img src="assets/img/<?php echo $detail['gambar']; ?>" 
                     alt="<?php echo $detail['nama_menu']; ?>" 
                     style="width: 150px; height: 180px; object-fit: cover; border-radius: 8px;">
                                    
                                </span>
                                <strong><?php echo $detail['nama_menu']; ?></strong>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: bold; color: var(--secondary-color);">
                                    <?php echo $detail['jumlah']; ?>x
                                </div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo format_rupiah($detail['subtotal']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="order-total">
                    <span>Total:</span>
                    <span style="color: var(--secondary-color);">
                        <?php echo format_rupiah($pesanan['total_harga']); ?>
                    </span>
                </div>
                
                <form method="POST" action="" style="margin-top: 1rem;">
                    <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                    
                    <?php if ($pesanan['status_pesanan'] == 'pending'): ?>
                        <button type="submit" name="update_status" value="diterima" 
                                class="btn btn-info" 
                                style="width: 100%; justify-content: center;">
                            ✅ Terima Pesanan
                        </button>
                    <?php elseif ($pesanan['status_pesanan'] == 'diterima'): ?>
                        <button type="submit" name="update_status" value="selesai" 
                                class="btn btn-success" 
                                style="width: 100%; justify-content: center;">
                            ✔️ Tandai Selesai
                        </button>
                    <?php endif; ?>
                </form>
                
                <?php if ($pesanan['tanggal_diterima']): ?>
                    <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem; text-align: center;">
                        Diterima: <?php echo date('H:i', strtotime($pesanan['tanggal_diterima'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<!-- Riwayat Pesanan Hari Ini -->
<?php
$today = date('Y-m-d');
$history_query = "SELECT p.*, u.nama as nama_user
                  FROM pesanan p
                  JOIN users u ON p.user_id = u.id
                  WHERE DATE(p.tanggal_pesanan) = '$today' 
                  AND p.status_pesanan = 'selesai'
                  ORDER BY p.tanggal_selesai DESC
                  LIMIT 10";
$history_result = mysqli_query($conn, $history_query);

if (mysqli_num_rows($history_result) > 0):
?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2 class="card-title">Pesanan Selesai Hari Ini</h2>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                        <tr>
                            <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                            <td><?php echo $row['nama_pelanggan']; ?></td>
                            <td>
                                <?php
                                $items_query = "SELECT COUNT(*) as total_items, SUM(jumlah) as total_qty 
                                               FROM detail_pesanan WHERE pesanan_id = {$row['id']}";
                                $items_result = mysqli_query($conn, $items_query);
                                $items = mysqli_fetch_assoc($items_result);
                                echo $items['total_qty'] . ' item';
                                ?>
                            </td>
                            <td><?php echo format_rupiah($row['total_harga']); ?></td>
                            <td><?php echo date('H:i', strtotime($row['tanggal_selesai'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
