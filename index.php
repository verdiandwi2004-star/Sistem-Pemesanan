<?php
session_start();
require_once 'includes/config.php';

// Jika sudah login, redirect ke home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        header('Location: home.php');
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BarokahCoffee</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .login-title {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-light);
        }
        
        .demo-info {
            background-color: var(--background-color);
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
        }
        
        .demo-info h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .demo-info p {
            margin: 0.3rem 0;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">☕</div>
                <h1 class="login-title">BarokahCoffee</h1>
                <p class="login-subtitle">Sistem Pemesanan Kopi</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Login
                </button>
            </form>
            
            <div class="demo-info">
                <h4>Akun Demo:</h4>
                <p><strong>Admin:</strong> admin / admin123</p>
                <p><strong>Kasir:</strong> kasir / kasir123</p>
                <p><strong>Dapur:</strong> dapur / dapur123</p>
                <p><strong>Pelanggan:</strong> customer / customer123</p>
            </div>
        </div>
    </div>
</body>
</html>
