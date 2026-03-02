<?php
require_once 'includes/header.php';

// Cek akses admin
if ($_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// Tambah kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = clean_input($_POST['nama_kategori']);
    
    $query = "INSERT INTO kategori_menu (nama_kategori) VALUES ('$nama_kategori')";
    if (mysqli_query($conn, $query)) {
        $success = 'Kategori berhasil ditambahkan!';
    } else {
        $error = 'Gagal menambahkan kategori!';
    }
}

// Edit kategori
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $nama_kategori = clean_input($_POST['nama_kategori']);
    
    $query = "UPDATE kategori_menu SET nama_kategori = '$nama_kategori' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = 'Kategori berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate kategori!';
    }
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Cek apakah kategori digunakan
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori_id = $id");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['total'] > 0) {
        $error = 'Kategori tidak dapat dihapus karena masih digunakan!';
    } else {
        $query = "DELETE FROM kategori_menu WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori!';
        }
    }
}

// Ambil semua kategori
$query = "SELECT k.*, COUNT(m.id) as total_menu 
          FROM kategori_menu k 
          LEFT JOIN menu m ON k.id = m.kategori_id 
          GROUP BY k.id 
          ORDER BY k.nama_kategori";
$result = mysqli_query($conn, $query);
?>

<h1 class="page-title">Kategori Menu</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Tambah Kategori Baru</h2>
    </div>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" class="form-control" required placeholder="Contoh: Hot Coffee">
        </div>
        <button type="submit" name="tambah" class="btn btn-primary">
            ➕ Tambah Kategori
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kategori</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Total Menu</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['nama_kategori']; ?></strong></td>
                        <td><?php echo $row['total_menu']; ?> menu</td>
                        <td><?php echo format_tanggal($row['created_at']); ?></td>
                        <td>
                            <button onclick="editKategori(<?php echo $row['id']; ?>, '<?php echo $row['nama_kategori']; ?>')" 
                                    class="btn btn-warning btn-sm">
                                ✏️ Edit
                            </button>
                            <?php if ($row['total_menu'] == 0): ?>
                                <a href="?hapus=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                    🗑️ Hapus
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Kategori</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
            </div>
            <button type="submit" name="edit" class="btn btn-primary">
                💾 Update Kategori
            </button>
        </form>
    </div>
</div>

<script>
function editKategori(id, nama) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
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
