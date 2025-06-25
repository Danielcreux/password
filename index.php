<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Inicio';
require_once 'templates/header.php';
?>

<div class="hero">
    <div class="container">
        <h1>Bienvenido al Gestor de Contraseñas Empresarial</h1>
        <p>Una solución segura para gestionar las credenciales de tu organización</p>
        
        <div class="cta-buttons">
            <a href="login.php" class="btn btn-primary">Iniciar sesión</a>
            <a href="register.php" class="btn btn-secondary">Registrarse</a>
        </div>
    </div>
</div>

<div class="features">
    <div class="container">
        <div class="feature">
            <i class="fas fa-shield-alt"></i>
            <h3>Seguridad</h3>
            <p>Todas las contraseñas se almacenan con encriptación AES-256</p>
        </div>
        
        <div class="feature">
            <i class="fas fa-users"></i>
            <h3>Control de acceso</h3>
            <p>Acceso restringido por departamentos y roles</p>
        </div>
        
        <div class="feature">
            <i class="fas fa-history"></i>
            <h3>Auditoría</h3>
            <p>Registro completo de todas las credenciales</p>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>