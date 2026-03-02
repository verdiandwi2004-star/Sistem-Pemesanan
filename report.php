<?php
require_once 'includes/header.php';

// Cek akses admin
if ($_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

// Default filter
$report_type = isset($_GET['type']) ? clean_input($_GET['type']) : 'all';
$tanggal_dari = isset($_GET['tanggal_dari']) ? clean_input($_GET['tanggal_dari']) : date('Y-m-d');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? clean_input($_GET['tanggal_sampai']) : date('Y-m-d');

// Build query berdasarkan tipe report
$where_clause = "WHERE 1=1";

switch ($report_type) {
    case 'daily':
        $where_clause .= " AND DATE(p.tanggal_pesanan) = '$tanggal_dari'";
        break;
    case 'weekly':
        $start_week = date('Y-m-d', strtotime('-7 days'));
        $where_clause .= " AND DATE(p.tanggal_pesanan) BETWEEN '$start_week' AND CURDATE()";
        break;
    case 'monthly':
        $month = date('Y-m');
        $where_clause .= " AND DATE_FORMAT(p.tanggal_pesanan, '%Y-%m') = '$month'";
        break;
    case 'range':
        $where_clause .= " AND DATE(p.tanggal_pesanan) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
        break;
}

// Query pesanan
$query = "SELECT p.*, u.nama as nama_user
          FROM pesanan p
          JOIN users u ON p.user_id = u.id
          $where_clause
          ORDER BY p.tanggal_pesanan DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik
$total_pesanan = mysqli_num_rows($result);
$total_revenue = 0;
$total_paid = 0;
$total_unpaid = 0;

mysqli_data_seek($result, 0);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['status_pembayaran'] == 'sudah_bayar') {
        $total_paid += $row['total_harga'];
    } else {
        $total_unpaid += $row['total_harga'];
    }
    $total_revenue += $row['total_harga'];
}

// Menu terpopuler
$menu_query = "SELECT m.nama_menu, m.gambar, SUM(dp.jumlah) as total_terjual, SUM(dp.subtotal) as total_pendapatan
               FROM detail_pesanan dp
               JOIN menu m ON dp.menu_id = m.id
               JOIN pesanan p ON dp.pesanan_id = p.id
               $where_clause
               GROUP BY dp.menu_id
               ORDER BY total_terjual DESC
               LIMIT 10";
$menu_result = mysqli_query($conn, $menu_query);
?>

<h1 class="page-title">Report & Analytics</h1>

<!-- Filter Report -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filter Report</h2>
    </div>
    
    <form method="GET" action="">
        <div class="grid grid-4">
            <div class="form-group">
                <label>Tipe Report</label>
                <select name="type" class="form-control" onchange="toggleDateRange(this.value)">
                    <option value="all" <?php echo $report_type == 'all' ? 'selected' : ''; ?>>Semua Pesanan</option>
                    <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>Harian</option>
                    <option value="weekly" <?php echo $report_type == 'weekly' ? 'selected' : ''; ?>>Mingguan (7 Hari)</option>
                    <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="range" <?php echo $report_type == 'range' ? 'selected' : ''; ?>>Range Tanggal</option>
                </select>
            </div>
            
            <div class="form-group" id="tanggal_dari_group">
                <label>Tanggal Dari</label>
                <input type="date" name="tanggal_dari" class="form-control" value="<?php echo $tanggal_dari; ?>">
            </div>
            
            <div class="form-group" id="tanggal_sampai_group" style="display: none;">
                <label>Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" class="form-control" value="<?php echo $tanggal_sampai; ?>">
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔍 Generate Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Statistik -->
<div class="grid grid-4">
    <div class="stat-card">
        <div class="stat-content">
            <h3>Total Pesanan</h3>
            <div class="stat-value"><?php echo $total_pesanan; ?></div>
        </div>
        <div class="stat-icon blue">📦</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-content">
            <h3>Total Revenue</h3>
            <div class="stat-value" style="font-size: 1.3rem;"><?php echo format_rupiah($total_revenue); ?></div>
        </div>
        <div class="stat-icon green">💰</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-content">
            <h3>Sudah Dibayar</h3>
            <div class="stat-value" style="font-size: 1.3rem;"><?php echo format_rupiah($total_paid); ?></div>
        </div>
        <div class="stat-icon purple">✅</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-content">
            <h3>Belum Dibayar</h3>
            <div class="stat-value" style="font-size: 1.3rem;"><?php echo format_rupiah($total_unpaid); ?></div>
        </div>
        <div class="stat-icon orange">⏳</div>
    </div>
</div>

<!-- Menu Terpopuler -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Top 10 Menu Terpopuler</h2>
        <a href="export_excel.php?type=menu&<?php echo http_build_query($_GET); ?>" class="btn btn-success btn-sm">
            📥 Download Excel
        </a>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Menu</th>
                    <th>Total Terjual</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while ($menu = mysqli_fetch_assoc($menu_result)): 
                ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; justify-content: center; 
                                        width: 35px; height: 35px; border-radius: 50%; 
                                        background-color: var(--secondary-color); color: white; font-weight: bold;">
                                <?php echo $rank++; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 1.5rem;"><?php echo $menu['gambar']; ?></span>
                                <strong><?php echo $menu['nama_menu']; ?></strong>
                            </div>
                        </td>
                        <td><strong><?php echo $menu['total_terjual']; ?> item</strong></td>
                        <td><strong><?php echo format_rupiah($menu['total_pendapatan']); ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Pesanan -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Detail Pesanan</h2>
        <a href="export_excel.php?type=pesanan&<?php echo http_build_query($_GET); ?>" class="btn btn-success btn-sm">
            📥 Download Excel
        </a>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])); ?></td>
                        <td>
                            <?php echo $row['nama_pelanggan']; ?>
                            <br>
                            <small style="color: #666;"><?php echo $row['no_telepon']; ?></small>
                        </td>
                        <td><strong><?php echo format_rupiah($row['total_harga']); ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo $row['status_pesanan']; ?>">
                                <?php echo ucfirst($row['status_pesanan']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $row['status_pembayaran'] == 'sudah_bayar' ? 'bayar' : 'belum'; ?>">
                                <?php echo $row['status_pembayaran'] == 'sudah_bayar' ? 'Sudah' : 'Belum'; ?>
                            </span>
                        </td>
                        <td><?php echo $row['metode_pembayaran'] ? ucfirst($row['metode_pembayaran']) : '-'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleDateRange(type) {
    const tanggalDari = document.getElementById('tanggal_dari_group');
    const tanggalSampai = document.getElementById('tanggal_sampai_group');
    
    if (type === 'daily' || type === 'range') {
        tanggalDari.style.display = 'block';
    } else {
        tanggalDari.style.display = 'none';
    }
    
    if (type === 'range') {
        tanggalSampai.style.display = 'block';
    } else {
        tanggalSampai.style.display = 'none';
    }
}

// Set initial state
toggleDateRange('<?php echo $report_type; ?>');
</script>

<?php require_once 'includes/footer.php'; ?>
