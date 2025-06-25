<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $pageTitle ?? 'Inicio' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <div class="logo">
                <h1><i class="fas fa-lock"></i> <?= APP_NAME ?></h1>
            </div>
            <nav class="main-nav">
                <?php if (isLoggedIn()): ?>
                    <span class="welcome">Hola, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="departments.php"><i class="fas fa-building"></i> Departamentos</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
                    <a href="register.php"><i class="fas fa-user-plus"></i> Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>