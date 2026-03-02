<?php
require_once 'includes/header.php';

// Cek akses admin
if ($_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// --- LOGIKA TAMBAH MENU ---
if (isset($_POST['tambah'])) {
    $nama_menu = clean_input($_POST['nama_menu']);
    $kategori_id = clean_input($_POST['kategori_id']);
    $harga = clean_input($_POST['harga']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    // Proses Gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    
    if ($gambar != "") {
        $ekstensi = pathinfo($gambar, PATHINFO_EXTENSION);
        $nama_file_baru = time() . '_' . str_replace(' ', '-', $nama_menu) . '.' . $ekstensi;
        $path = "assets/img/" . $nama_file_baru;

        if (move_uploaded_file($tmp_name, $path)) {
            $query = "INSERT INTO menu (nama_menu, kategori_id, harga, deskripsi, gambar, status) 
                      VALUES ('$nama_menu', $kategori_id, $harga, '$deskripsi', '$nama_file_baru', 'tersedia')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Menu berhasil ditambahkan!';
            } else {
                $error = 'Gagal menyimpan ke database: ' . mysqli_error($conn);
            }
        } else {
            $error = 'Gagal upload gambar ke folder assets/img/. Pastikan folder ada dan dapat diakses.';
        }
    } else {
        $error = 'Silahkan pilih gambar terlebih dahulu!';
    }
}

// --- LOGIKA EDIT MENU ---
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $nama_menu = clean_input($_POST['nama_menu']);
    $kategori_id = clean_input($_POST['kategori_id']);
    $harga = clean_input($_POST['harga']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $status = clean_input($_POST['status']);
    $gambar_lama = $_POST['gambar_lama'];
    
    $gambar_baru = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];

    if ($gambar_baru != "") {
        // Jika user upload gambar baru
        $ekstensi = pathinfo($gambar_baru, PATHINFO_EXTENSION);
        $nama_file_baru = time() . '_' . str_replace(' ', '-', $nama_menu) . '.' . $ekstensi;
        $path = "assets/img/" . $nama_file_baru;

        if (move_uploaded_file($tmp_name, $path)) {
            $gambar_final = $nama_file_baru;
            // Hapus file lama jika ada (agar hosting tidak penuh)
            if ($gambar_lama && file_exists("assets/img/" . $gambar_lama)) {
                unlink("assets/img/" . $gambar_lama);
            }
        } else {
            $gambar_final = $gambar_lama;
        }
    } else {
        // Jika tidak ganti gambar, pakai nama file lama
        $gambar_final = $gambar_lama;
    }

    $query = "UPDATE menu SET 
              nama_menu = '$nama_menu',
              kategori_id = $kategori_id,
              harga = $harga,
              deskripsi = '$deskripsi',
              gambar = '$gambar_final',
              status = '$status'
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        $success = 'Menu berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate menu: ' . mysqli_error($conn);
    }
}

// --- LOGIKA HAPUS MENU ---
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Hapus file gambar dari folder sebelum hapus data di DB
    $res = mysqli_query($conn, "SELECT gambar FROM menu WHERE id = $id");
    $data = mysqli_fetch_assoc($res);
    if ($data && file_exists("assets/img/" . $data['gambar'])) {
        unlink("assets/img/" . $data['gambar']);
    }

    $query = "DELETE FROM menu WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = 'Menu berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus menu!';
    }
}

// Ambil kategori untuk dropdown
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori_menu ORDER BY nama_kategori");

// Filter
$filter_kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Query ambil semua menu
$query = "SELECT m.*, k.nama_kategori 
          FROM menu m 
          JOIN kategori_menu k ON m.kategori_id = k.id";

$where = [];
if ($filter_kategori) $where[] = "m.kategori_id = $filter_kategori";
if ($filter_status) $where[] = "m.status = '$filter_status'";

if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY k.nama_kategori, m.nama_menu";
$result = mysqli_query($conn, $query);
?>

<h1 class="page-title">Daftar Menu</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Tambah Menu Baru</h2>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-2">
            <div class="form-group">
                <label>Nama Menu</label>
                <input type="text" name="nama_menu" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori_id" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                    <?php 
                    mysqli_data_seek($kategori_result, 0);
                    while ($kat = mysqli_fetch_assoc($kategori_result)): 
                    ?>
                        <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="harga" class="form-control" required min="0">
            </div>
            
            <div class="form-group">
                <label>Foto Menu</label>
                <input type="file" name="gambar" class="form-control" accept="image/*" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
        </div>
        
        <button type="submit" name="tambah" class="btn btn-primary">
            ➕ Tambah Menu
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filter Menu</h2>
    </div>
    <form method="GET" action="">
        <div class="grid grid-3">
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php 
                    mysqli_data_seek($kategori_result, 0);
                    while ($kat = mysqli_fetch_assoc($kategori_result)): 
                    ?>
                        <option value="<?php echo $kat['id']; ?>" <?php echo $filter_kategori == $kat['id'] ? 'selected' : ''; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="tersedia" <?php echo $filter_status == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="habis" <?php echo $filter_status == 'habis' ? 'selected' : ''; ?>>Habis</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">🔍 Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="grid grid-3">
    <?php while ($menu = mysqli_fetch_assoc($result)): ?>
        <div class="menu-item">
            <div class="menu-image-container">
                <img src="assets/img/<?php echo $menu['gambar']; ?>" 
                     alt="<?php echo $menu['nama_menu']; ?>" 
                     style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px;">
            </div>
            <div class="menu-name"><?php echo $menu['nama_menu']; ?></div>
            <div class="menu-category"><?php echo $menu['nama_kategori']; ?></div>
            <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0; height: 40px; overflow: hidden;">
                <?php echo $menu['deskripsi']; ?>
            </p>
            <div class="menu-price"><?php echo format_rupiah($menu['harga']); ?></div>
            <div style="margin-top: 1rem;">
                <span class="badge <?php echo $menu['status'] == 'tersedia' ? 'badge-selesai' : 'badge-belum'; ?>">
                    <?php echo ucfirst($menu['status']); ?>
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button onclick="editMenu(<?php echo htmlspecialchars(json_encode($menu)); ?>)" 
                        class="btn btn-warning btn-sm" style="flex: 1;">
                    ✏️ Edit
                </button>
                <a href="?hapus=<?php echo $menu['id']; ?>" 
                   class="btn btn-danger btn-sm" 
                   style="flex: 1;"
                   onclick="return confirm('Yakin ingin menghapus menu ini?')">
                    🗑️ Hapus
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Menu</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori_id" id="edit_kategori" class="form-control" required>
                        <?php 
                        mysqli_data_seek($kategori_result, 0);
                        while ($kat = mysqli_fetch_assoc($kategori_result)): 
                        ?>
                            <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" id="edit_harga" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Ganti Foto (Biarkan kosong jika tidak diganti)</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
            </div>
            
            <button type="submit" name="edit" class="btn btn-primary">
                💾 Update Menu
            </button>
        </form>
    </div>
</div>

<script>
function editMenu(menu) {
    document.getElementById('edit_id').value = menu.id;
    document.getElementById('edit_nama').value = menu.nama_menu;
    document.getElementById('edit_kategori').value = menu.kategori_id;
    document.getElementById('edit_harga').value = menu.harga;
    document.getElementById('edit_deskripsi').value = menu.deskripsi;
    document.getElementById('edit_status').value = menu.status;
    document.getElementById('edit_gambar_lama').value = menu.gambar; // Simpan nama file lama
    
    document.getElementById('editModal').classList.add('show');
}

function closeModal() {
    document.getElementById('editModal').classList.remove('show');
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>