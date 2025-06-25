<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Registro';
require_once 'templates/header.php';

require_once 'includes/db.php';
$departments = getDepartments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $departmentId = $_POST['department_id'] ?? null;
    
    // Validaciones
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'El nombre de usuario es requerido';
    }
    
    if (empty($email)) {
        $errors[] = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida';
    } elseif (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    // Verificar si el usuario ya existe
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if ($result->fetchArray()) {
        $errors[] = 'El usuario o email ya está registrado';
    }
    
    if (empty($errors)) {
        // Registrar usuario
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare('INSERT INTO users (username, email, password, department_id) 
                             VALUES (:username, :email, :password, :department_id)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(':department_id', $departmentId, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Registro exitoso. Ahora puedes iniciar sesión.';
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Error al registrar el usuario';
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Registro de usuario</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="department_id">Departamento:</label>
                <select id="department_id" name="department_id">
                    <option value="">Seleccione un departamento</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
        
        <p class="auth-link">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>