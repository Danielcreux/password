<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();
checkPermission('admin');

$pageTitle = 'Gestión de Departamentos';
require_once 'templates/header.php';

require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$deptId = $_GET['dept_id'] ?? 0;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        // Procesar departamento
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $_SESSION['error'] = 'El nombre del departamento es requerido';
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare('INSERT INTO departments (name, description) VALUES (:name, :description)');
            } else {
                $stmt = $db->prepare('UPDATE departments SET name = :name, description = :description WHERE id = :id');
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            }
            
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = $action === 'add' ? 'Departamento creado correctamente' : 'Departamento actualizado correctamente';
                header('Location: departments.php');
                exit;
            } else {
                $_SESSION['error'] = 'Error al guardar el departamento';
            }
        }
    } elseif ($action === 'add_app' || $action === 'edit_app') {
        // Procesar aplicación
        $name = trim($_POST['name']);
        $url = trim($_POST['url']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $notes = trim($_POST['notes']);
        $departmentId = $action === 'add_app' ? $deptId : $_POST['department_id'];
        
        if (empty($name) || empty($password)) {
            $_SESSION['error'] = 'El nombre y la contraseña son requeridos';
        } else {
            $encryptedPassword = encryptPassword($password);
            
            if ($action === 'add_app') {
                $stmt = $db->prepare('INSERT INTO applications 
                                    (name, url, department_id, username, password, notes) 
                                    VALUES (:name, :url, :department_id, :username, :password, :notes)');
            } else {
                $stmt = $db->prepare('UPDATE applications SET 
                                    name = :name, 
                                    url = :url, 
                                    department_id = :department_id, 
                                    username = :username, 
                                    password = :password, 
                                    notes = :notes 
                                    WHERE id = :id');
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            }
            
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':url', $url, SQLITE3_TEXT);
            $stmt->bindValue(':department_id', $departmentId, SQLITE3_INTEGER);
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':password', $encryptedPassword, SQLITE3_TEXT);
            $stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = $action === 'add_app' ? 'Aplicación agregada correctamente' : 'Aplicación actualizada correctamente';
                header('Location: departments.php');
                exit;
            } else {
                $_SESSION['error'] = 'Error al guardar la aplicación';
            }
        }
    }
}

// Manejar eliminación de aplicaciones
if ($action === 'delete_app' && isset($_GET['id'])) {
    checkPermission('admin');
    
    if (deleteApplication($_GET['id'])) {
        $_SESSION['success'] = 'Aplicación eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la aplicación o no existe';
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
    exit;
}

// Manejar eliminación de departamentos
if ($action === 'delete_dept' && isset($_GET['id'])) {
    checkPermission('admin');
    
    if (deleteDepartment($_GET['id'])) {
        $_SESSION['success'] = 'Departamento eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el departamento. Asegúrate que no tenga aplicaciones asociadas.';
    }
    header('Location: departments.php');
    exit;
}

// Mostrar formularios
if ($action === 'add' || $action === 'edit') {
    $department = [];
    if ($action === 'edit') {
        $stmt = $db->prepare('SELECT * FROM departments WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $department = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$department) {
            $_SESSION['error'] = 'Departamento no encontrado';
            header('Location: departments.php');
            exit;
        }
    }

    
    ?>
    <div class="form-container">
        <h2><?= $action === 'add' ? 'Agregar Departamento' : 'Editar Departamento' ?></h2>
        
        <form method="POST" action="departments.php?action=<?= $action ?><?= $action === 'edit' ? '&id='.$id : '' ?>">
            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($department['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea id="description" name="description"><?= htmlspecialchars($department['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="departments.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php
} elseif ($action === 'add_app' || $action === 'edit_app') {
    $app = [];
    $departments = getDepartments();
    
    if ($action === 'edit_app') {
        $stmt = $db->prepare('SELECT * FROM applications WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $app = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$app) {
            $_SESSION['error'] = 'Aplicación no encontrada';
            header('Location: departments.php');
            exit;
        }
    }
    
    ?>
    <div class="form-container">
        <h2><?= $action === 'add_app' ? 'Agregar Aplicación' : 'Editar Aplicación' ?></h2>
        
        <form method="POST" action="departments.php?action=<?= $action ?><?= $action === 'edit_app' ? '&id='.$id : '&dept_id='.$deptId ?>">
            <?php if ($action === 'edit_app'): ?>
                <div class="form-group">
                    <label for="department_id">Departamento:</label>
                    <select id="department_id" name="department_id" required>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $app['department_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Nombre de la aplicación:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($app['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="url">URL (opcional):</label>
                <input type="url" id="url" name="url" value="<?= htmlspecialchars($app['url'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="username">Usuario (opcional):</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($app['username'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" value="<?= !empty($app) ? decryptPassword($app['password']) : '' ?>" required>
                    <button type="button" class="btn-icon toggle-password"><i class="fas fa-eye"></i></button>
                    <button type="button" class="btn-icon generate-password"><i class="fas fa-random"></i></button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Notas (opcional):</label>
                <textarea id="notes" name="notes"><?= htmlspecialchars($app['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="departments.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php
} else {
    // Lista de departamentos
    $departments = getDepartments();
    ?>
    <div class="departments-header">
        <h2>Gestión de Departamentos</h2>
        <a href="departments.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Departamento
        </a>
    </div>
    
    <div class="departments-list">
        <?php if (empty($departments)): ?>
            <p>No hay departamentos registrados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Aplicaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <?php
                        $stmt = $db->prepare('SELECT COUNT(*) as count FROM applications WHERE department_id = :dept_id');
                        $stmt->bindValue(':dept_id', $dept['id'], SQLITE3_INTEGER);
                        $result = $stmt->execute();
                        $appCount = $result->fetchArray(SQLITE3_ASSOC)['count'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($dept['name']) ?></td>
                            <td><?= htmlspecialchars($dept['description']) ?></td>
                            <td><?= $appCount ?></td>
                            <td class="actions">
                                <a href="departments.php?action=edit&id=<?= $dept['id'] ?>" class="btn-icon">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="departments.php?action=add_app&dept_id=<?= $dept['id'] ?>" class="btn-icon">
                                    <i class="fas fa-plus"></i>
                                </a>
                                <a href="#" class="btn-icon delete-dept" data-id="<?= $dept['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="dashboard.php?dept=<?= $dept['id'] ?>" class="btn-icon">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

require_once 'templates/footer.php';
?>