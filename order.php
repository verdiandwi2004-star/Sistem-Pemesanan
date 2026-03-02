<?php
require_once 'includes/header.php';

// Cek akses - hanya kasir, pelanggan, dan admin
if (!in_array($_SESSION['role'], ['kasir', 'pelanggan', 'admin'])) {
    header('Location: home.php');
    exit();
}

$success = '';
$error = '';

// Proses pemesanan
if (isset($_POST['pesan'])) {
    $nama_pelanggan = clean_input($_POST['nama_pelanggan']);
    $no_telepon = clean_input($_POST['no_telepon']);
    $cart = json_decode($_POST['cart_data'], true);
    
    if (empty($cart)) {
        $error = 'Keranjang kosong!';
    } else {
        // Generate kode pesanan
        $kode_pesanan = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Hitung total
        $total_harga = 0;
        foreach ($cart as $item) {
            $total_harga += $item['harga'] * $item['jumlah'];
        }
        
        // Insert pesanan
        $query = "INSERT INTO pesanan (kode_pesanan, user_id, nama_pelanggan, no_telepon, total_harga) 
                  VALUES ('$kode_pesanan', {$_SESSION['user_id']}, '$nama_pelanggan', '$no_telepon', $total_harga)";
        
        if (mysqli_query($conn, $query)) {
            $pesanan_id = mysqli_insert_id($conn);
            
            // Insert detail pesanan
            foreach ($cart as $item) {
                $menu_id = $item['id'];
                $jumlah = $item['jumlah'];
                $harga = $item['harga'];
                $subtotal = $harga * $jumlah;
                
                $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga, subtotal) 
                                 VALUES ($pesanan_id, $menu_id, $jumlah, $harga, $subtotal)";
                mysqli_query($conn, $query_detail);
            }
            
            $success = "Pesanan berhasil dibuat dengan kode: <strong>$kode_pesanan</strong>";
            echo "<script>localStorage.removeItem('cart');</script>";
        } else {
            $error = 'Gagal membuat pesanan!';
        }
    }
}

// Ambil menu yang tersedia
$query_menu = "SELECT m.*, k.nama_kategori 
               FROM menu m 
               JOIN kategori_menu k ON m.kategori_id = k.id 
               WHERE m.status = 'tersedia'
               ORDER BY k.nama_kategori, m.nama_menu";
$result_menu = mysqli_query($conn, $query_menu);

// Group menu by kategori
$menu_by_kategori = [];
while ($menu = mysqli_fetch_assoc($result_menu)) {
    $menu_by_kategori[$menu['nama_kategori']][] = $menu;
}
?>

<h1 class="page-title">Buat Pesanan Baru</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Menu List -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Pilih Menu</h2>
            </div>
            
            <?php foreach ($menu_by_kategori as $kategori => $menus): ?>
                <h3 style="color: var(--primary-color); margin: 1.5rem 0 1rem 0; font-size: 1.2rem;">
                    <?php echo $kategori; ?>
                </h3>
                
                <div class="grid grid-2" style="margin-bottom: 1.5rem;">
                    <?php foreach ($menus as $menu): ?>
                        <div class="menu-item">
                            <div class="menu-icon" ><img src="assets/img/<?php echo $menu['gambar']; ?>" alt="<?php echo $menu['nama_menu']; ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px;"></div>
                            <div class="menu-name"><?php echo $menu['nama_menu']; ?></div>
                            <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0;">
                                <?php echo $menu['deskripsi']; ?>
                            </p>
                            <div class="menu-price"><?php echo format_rupiah($menu['harga']); ?></div>
                            <button onclick="addToCart(<?php echo htmlspecialchars(json_encode($menu)); ?>)" 
                                    class="btn btn-primary" 
                                    style="width: 100%; margin-top: 1rem; justify-content: center;">
                                ➕ Tambah
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Cart -->
    <div>
        <div class="card cart-container">
            <div class="card-header">
                <h2 class="card-title">Keranjang</h2>
            </div>
            
            <form method="POST" action="" id="orderForm">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" class="form-control" 
                           value="<?php echo $_SESSION['role'] == 'pelanggan' ? $_SESSION['nama'] : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="tel" name="no_telepon" class="form-control" required>
                </div>
                
                <div id="cartItems" style="margin: 1rem 0;">
                    <p style="text-align: center; color: #999; padding: 2rem 0;">Keranjang kosong</p>
                </div>
                
                <div class="order-total" style="margin-top: 1rem;">
                    <span>Total:</span>
                    <span id="totalPrice">Rp 0</span>
                </div>
                
                <input type="hidden" name="cart_data" id="cartData">
                
                <button type="submit" name="pesan" class="btn btn-success" 
                        style="width: 100%; margin-top: 1rem; justify-content: center;"
                        id="submitBtn" disabled>
                    🛒 Buat Pesanan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function addToCart(menu) {
    const existingItem = cart.find(item => item.id === menu.id);
    
    if (existingItem) {
        existingItem.jumlah++;
    } else {
        cart.push({
            id: menu.id,
            nama_menu: menu.nama_menu,
            harga: parseFloat(menu.harga),
            jumlah: 1
        });
    }
    
    updateCart();
}

function updateQuantity(menuId, change) {
    // Cari index item dalam array cart
    const itemIndex = cart.findIndex(item => item.id == menuId);
    
    if (itemIndex !== -1) {
        cart[itemIndex].jumlah += change;
        
        // JIKA JUMLAH 0 ATAU KURANG, HAPUS DARI ARRAY
        if (cart[itemIndex].jumlah <= 0) {
            cart.splice(itemIndex, 1);
        }
    }
    
    // Refresh tampilan keranjang
    updateCart();
}

function removeFromCart(menuId) {
    cart = cart.filter(item => item.id !== menuId);
    updateCart();
}

function updateCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    
    const cartItemsDiv = document.getElementById('cartItems');
    const totalPriceSpan = document.getElementById('totalPrice');
    const cartDataInput = document.getElementById('cartData');
    const submitBtn = document.getElementById('submitBtn');
    
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem 0;">Keranjang kosong</p>';
        totalPriceSpan.textContent = 'Rp 0';
        submitBtn.disabled = true;
    } else {
        let total = 0;
        let html = '';
        
        cart.forEach(item => {
            const subtotal = item.harga * item.jumlah;
            total += subtotal;
            
            html += `
                <div class="cart-item">
                    <div style="flex: 1;">
                        <div style="font-weight: bold;">${item.nama_menu}</div>
                        <div style="font-size: 0.85rem; color: #666;">
                            ${formatRupiah(item.harga)} x ${item.jumlah}
                        </div>
                    </div>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn" 
                                style="background: #ddd;" 
                                onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span style="font-weight: bold;">${item.jumlah}</span>
                        <button type="button" class="quantity-btn" 
                                style="background: var(--secondary-color); color: white;" 
                                onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                </div>
            `;
        });


        
        cartItemsDiv.innerHTML = html;
        totalPriceSpan.textContent = formatRupiah(total);
        submitBtn.disabled = false;
    }
    
    cartDataInput.value = JSON.stringify(cart);
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Load cart on page load
updateCart();
</script>

<?php require_once 'includes/footer.php'; ?>
