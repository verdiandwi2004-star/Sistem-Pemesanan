<?php
require_once 'includes/header.php';

// Cek akses admin
if ($_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// Tambah user
if (isset($_POST['tambah'])) {
    $username = clean_input($_POST['username']);
    $password = md5($_POST['password']);
    $nama = clean_input($_POST['nama']);
    $role = clean_input($_POST['role']);
    
    // Cek username sudah ada
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO users (username, password, nama, role) 
                  VALUES ('$username', '$password', '$nama', '$role')";
        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan user!';
        }
    }
}

// Edit user
if (isset($_POST['edit'])) {
    $id = clean_input($_POST['id']);
    $username = clean_input($_POST['username']);
    $nama = clean_input($_POST['nama']);
    $role = clean_input($_POST['role']);
    
    $query = "UPDATE users SET 
              username = '$username',
              nama = '$nama',
              role = '$role'";
    
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $query .= ", password = '$password'";
    }
    
    $query .= " WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $success = 'User berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate user!';
    }
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Tidak bisa hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $error = 'Anda tidak dapat menghapus akun sendiri!';
    } else {
        $query = "DELETE FROM users WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user!';
        }
    }
}

// Ambil semua user
$query = "SELECT * FROM users ORDER BY role, nama";
$result = mysqli_query($conn, $query);
?>

<h1 class="page-title">Manajemen User</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Tambah User Baru</h2>
    </div>
    
    <form method="POST" action="">
        <div class="grid grid-2">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="">Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                    <option value="dapur">Dapur</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>
        </div>
        
        <button type="submit" name="tambah" class="btn btn-primary">
            ➕ Tambah User
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar User</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
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
                        <td><strong><?php echo $row['username']; ?></strong></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $row['role']; ?>" 
                                  style="background-color: 
                                    <?php 
                                    if ($row['role'] == 'admin') echo '#dc3545';
                                    elseif ($row['role'] == 'kasir') echo '#28a745';
                                    elseif ($row['role'] == 'dapur') echo '#ffc107';
                                    else echo '#17a2b8';
                                    ?>; color: white;">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td><?php echo format_tanggal($row['created_at']); ?></td>
                        <td>
                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                    class="btn btn-warning btn-sm">
                                ✏️ Edit
                            </button>
                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                <a href="?hapus=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus user ini?')">
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
            <h2>Edit User</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" id="edit_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="dapur">Dapur</option>
                        <option value="pelanggan">Pelanggan</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="edit" class="btn btn-primary">
                💾 Update User
            </button>
        </form>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_nama').value = user.nama;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_password').value = '';
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
