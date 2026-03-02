<?php
require_once 'includes/config.php';
session_start();

// Cek akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('Akses ditolak!');
}

$export_type = isset($_GET['type']) ? clean_input($_GET['type']) : 'pesanan';
$report_type = isset($_GET['type']) ? clean_input($_GET['type']) : 'all';
$tanggal_dari = isset($_GET['tanggal_dari']) ? clean_input($_GET['tanggal_dari']) : date('Y-m-d');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? clean_input($_GET['tanggal_sampai']) : date('Y-m-d');

// Build where clause
$where_clause = "WHERE 1=1";

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
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
}

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
$filename = "BarokahCoffee_Report_" . date('YmdHis') . ".xls";
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM

if ($export_type == 'menu') {
    // Export Menu Terpopuler
    $query = "SELECT m.nama_menu, k.nama_kategori, SUM(dp.jumlah) as total_terjual, 
              SUM(dp.subtotal) as total_pendapatan, AVG(dp.harga) as harga_rata
              FROM detail_pesanan dp
              JOIN menu m ON dp.menu_id = m.id
              JOIN kategori_menu k ON m.kategori_id = k.id
              JOIN pesanan p ON dp.pesanan_id = p.id
              $where_clause
              GROUP BY dp.menu_id
              ORDER BY total_terjual DESC";
    
    $result = mysqli_query($conn, $query);
    
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th colspan='5' style='text-align:center; font-size:16px; font-weight:bold;'>BAROKAHCOFFEE - REPORT MENU TERPOPULER</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<th colspan='5' style='text-align:center;'>Tanggal Export: " . date('d/m/Y H:i') . "</th>";
    echo "</tr>";
    echo "<tr><th colspan='5'></th></tr>";
    echo "<tr style='background-color:#143668; color:white; font-weight:bold;'>";
    echo "<th>Rank</th>";
    echo "<th>Nama Menu</th>";
    echo "<th>Kategori</th>";
    echo "<th>Total Terjual</th>";
    echo "<th>Total Pendapatan</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $rank = 1;
    $total_all = 0;
    $total_pendapatan_all = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='text-align:center;'>" . $rank++ . "</td>";
        echo "<td>" . $row['nama_menu'] . "</td>";
        echo "<td>" . $row['nama_kategori'] . "</td>";
        echo "<td style='text-align:right;'>" . $row['total_terjual'] . "</td>";
        echo "<td style='text-align:right;'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
        echo "</tr>";
        
        $total_all += $row['total_terjual'];
        $total_pendapatan_all += $row['total_pendapatan'];
    }
    
    echo "<tr style='background-color:#f0f0f0; font-weight:bold;'>";
    echo "<td colspan='3' style='text-align:right;'>TOTAL:</td>";
    echo "<td style='text-align:right;'>" . $total_all . "</td>";
    echo "<td style='text-align:right;'>Rp " . number_format($total_pendapatan_all, 0, ',', '.') . "</td>";
    echo "</tr>";
    
    echo "</tbody>";
    echo "</table>";
    
} else {
    // Export Pesanan
    $query = "SELECT p.*, u.nama as nama_user
              FROM pesanan p
              JOIN users u ON p.user_id = u.id
              $where_clause
              ORDER BY p.tanggal_pesanan DESC";
    
    $result = mysqli_query($conn, $query);
    
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th colspan='10' style='text-align:center; font-size:16px; font-weight:bold;'>BAROKAHCOFFEE - REPORT PESANAN</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<th colspan='10' style='text-align:center;'>Tanggal Export: " . date('d/m/Y H:i') . "</th>";
    echo "</tr>";
    echo "<tr><th colspan='10'></th></tr>";
    echo "<tr style='background-color:#143668; color:white; font-weight:bold;'>";
    echo "<th>No</th>";
    echo "<th>Kode Pesanan</th>";
    echo "<th>Tanggal Pesanan</th>";
    echo "<th>Nama Pelanggan</th>";
    echo "<th>No. Telepon</th>";
    echo "<th>Total</th>";
    echo "<th>Status Pesanan</th>";
    echo "<th>Status Pembayaran</th>";
    echo "<th>Metode Pembayaran</th>";
    echo "<th>Dibuat Oleh</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $no = 1;
    $total_revenue = 0;
    $total_paid = 0;
    $total_unpaid = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='text-align:center;'>" . $no++ . "</td>";
        echo "<td>" . $row['kode_pesanan'] . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])) . "</td>";
        echo "<td>" . $row['nama_pelanggan'] . "</td>";
        echo "<td>" . $row['no_telepon'] . "</td>";
        echo "<td style='text-align:right;'>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
        echo "<td>" . ucfirst($row['status_pesanan']) . "</td>";
        echo "<td>" . ($row['status_pembayaran'] == 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar') . "</td>";
        echo "<td>" . ($row['metode_pembayaran'] ? ucfirst($row['metode_pembayaran']) : '-') . "</td>";
        echo "<td>" . $row['nama_user'] . "</td>";
        echo "</tr>";
        
        $total_revenue += $row['total_harga'];
        if ($row['status_pembayaran'] == 'sudah_bayar') {
            $total_paid += $row['total_harga'];
        } else {
            $total_unpaid += $row['total_harga'];
        }
    }
    
    echo "<tr><td colspan='10'></td></tr>";
    echo "<tr style='background-color:#f0f0f0; font-weight:bold;'>";
    echo "<td colspan='5' style='text-align:right;'>TOTAL REVENUE:</td>";
    echo "<td style='text-align:right;'>Rp " . number_format($total_revenue, 0, ',', '.') . "</td>";
    echo "<td colspan='4'></td>";
    echo "</tr>";
    echo "<tr style='background-color:#d4edda; font-weight:bold;'>";
    echo "<td colspan='5' style='text-align:right;'>SUDAH DIBAYAR:</td>";
    echo "<td style='text-align:right;'>Rp " . number_format($total_paid, 0, ',', '.') . "</td>";
    echo "<td colspan='4'></td>";
    echo "</tr>";
    echo "<tr style='background-color:#f8d7da; font-weight:bold;'>";
    echo "<td colspan='5' style='text-align:right;'>BELUM DIBAYAR:</td>";
    echo "<td style='text-align:right;'>Rp " . number_format($total_unpaid, 0, ',', '.') . "</td>";
    echo "<td colspan='4'></td>";
    echo "</tr>";
    
    echo "</tbody>";
    echo "</table>";
    
    // Detail items per pesanan
    echo "<br><br>";
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th colspan='6' style='text-align:center; font-size:16px; font-weight:bold;'>DETAIL ITEMS PER PESANAN</th>";
    echo "</tr>";
    echo "<tr style='background-color:#143668; color:white; font-weight:bold;'>";
    echo "<th>Kode Pesanan</th>";
    echo "<th>Nama Menu</th>";
    echo "<th>Kategori</th>";
    echo "<th>Harga Satuan</th>";
    echo "<th>Jumlah</th>";
    echo "<th>Subtotal</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        $detail_query = "SELECT dp.*, m.nama_menu, k.nama_kategori 
                        FROM detail_pesanan dp 
                        JOIN menu m ON dp.menu_id = m.id 
                        JOIN kategori_menu k ON m.kategori_id = k.id
                        WHERE dp.pesanan_id = {$row['id']}";
        $detail_result = mysqli_query($conn, $detail_query);
        
        $first = true;
        while ($detail = mysqli_fetch_assoc($detail_result)) {
            echo "<tr>";
            if ($first) {
                echo "<td rowspan='" . mysqli_num_rows($detail_result) . "'><strong>" . $row['kode_pesanan'] . "</strong></td>";
                $first = false;
            }
            echo "<td>" . $detail['nama_menu'] . "</td>";
            echo "<td>" . $detail['nama_kategori'] . "</td>";
            echo "<td style='text-align:right;'>Rp " . number_format($detail['harga'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center;'>" . $detail['jumlah'] . "</td>";
            echo "<td style='text-align:right;'>Rp " . number_format($detail['subtotal'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }
    }
    
    echo "</tbody>";
    echo "</table>";
}

mysqli_close($conn);
?>
