<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirigir si no está logueado
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Obtener información del usuario actual
function getCurrentUser() {
    global $db;
    if (!isLoggedIn()) return null;
    
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}
?>