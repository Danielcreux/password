<?php
try {
    $db = new SQLite3('database.db');
    
    // Tabla de usuarios
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "user",
        department_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Tabla de departamentos
    $db->exec('CREATE TABLE IF NOT EXISTS departments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Tabla de aplicaciones
    $db->exec('CREATE TABLE IF NOT EXISTS applications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT,
        department_id INTEGER NOT NULL,
        username TEXT,
        password TEXT NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )');
    
    // Insertar usuario admin por defecto (contraseña: admin123)
    $hashedPassword = password_hash('danielcreux', PASSWORD_BCRYPT);
    $db->exec("INSERT OR IGNORE INTO users (username, email, password, role) 
               VALUES ('danielcreux', 'info@daniel.com', '$hashedPassword', 'admin')");
    
    echo "Base de datos inicializada correctamente. <a href='index.php'>Ir al inicio</a>";
    
} catch (Exception $e) {
    die("Error al inicializar la base de datos: " . $e->getMessage());
}
?>