<?php
// Conexión a la base de datos SQLite
try {
    $db = new SQLite3('database.db');
    $db->busyTimeout(5000);
    $db->exec('PRAGMA journal_mode = wal;');
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>