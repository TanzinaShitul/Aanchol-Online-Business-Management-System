<?php
require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Debug: Check what's being received
    error_log("Login attempt - Email: $email, Password: " . (empty($password) ? 'empty' : 'provided'));
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        $sql = "SELECT * FROM users WHERE email = :email AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Check password - For testing, you can use plain text check first
            // For production, always use password_verify()
            
            // TEMPORARY: For testing with plain password
            if ($password === '1234') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                redirect('dashboard.php');
            } 
            // For hashed password (production)
            else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                redirect('dashboard.php');
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Admin Login - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css">
     <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card p-4">
                    <div class="text-center mb-4">
                        <h2 class="text-primary">AANCHOL</h2>
                        <h4>Admin Panel</h4>
                        <p class="text-muted">Please sign in to continue</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                   required>
                        </div>
                        <div class="mb-3">
                             <label for="password" class="form-label">Password</label>
                                <div class="form-control-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> Restricted Access
                        </small>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/login.js"></script>
</body>
</html>