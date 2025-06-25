<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Iniciar sesión';
require_once 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validar credenciales
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['department_id'] = $user['department_id'];
        
        $_SESSION['success'] = 'Has iniciado sesión correctamente';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Iniciar sesión</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar sesión</button>
        </form>
        
        <p class="auth-link">¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>