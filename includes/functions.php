<?php
require_once 'config.php';

// Función para encriptar contraseñas (usando AES-256-CBC)
function encryptPassword($password) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($password, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Función para desencriptar contraseñas
function decryptPassword($encryptedPassword) {
    list($encryptedData, $iv) = explode('::', base64_decode($encryptedPassword), 2);
    return openssl_decrypt($encryptedData, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

// Función para verificar permisos
function checkPermission($requiredRole = 'user') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    if ($requiredRole === 'admin' && $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
        header('Location: dashboard.php');
        exit;
    }
}

// Función para obtener departamentos
function getDepartments() {
    global $db;
    $query = $db->query('SELECT * FROM departments ORDER BY name');
    $departments = [];
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $departments[] = $row;
    }
    return $departments;
}

// Función para obtener aplicaciones por departamento
function getApplicationsByDepartment($departmentId) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM applications WHERE department_id = :department_id ORDER BY name');
    $stmt->bindValue(':department_id', $departmentId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $applications = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $applications[] = $row;
    }
    return $applications;
}

// Función para eliminar una aplicación
function deleteApplication($id) {
    global $db;
    
    try {
        $stmt = $db->prepare('DELETE FROM applications WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error al eliminar aplicación: " . $e->getMessage());
        return false;
    }
}

// Función para eliminar un departamento
function deleteDepartment($id) {
    global $db;
    
    try {
        // Primero verificamos que no tenga aplicaciones asociadas
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM applications WHERE department_id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
        
        if ($count > 0) {
            return false; // No se puede eliminar si tiene aplicaciones
        }
        
        // Eliminar el departamento
        $stmt = $db->prepare('DELETE FROM departments WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error al eliminar departamento: " . $e->getMessage());
        return false;
    }
}
?>