<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

$pageTitle = 'Panel de control';
require_once 'templates/header.php';

require_once 'includes/db.php';
require_once 'includes/functions.php';

$user = getCurrentUser();
$isAdmin = $_SESSION['role'] === 'admin';

// Obtener departamentos y aplicaciones según el rol
if ($isAdmin) {
    $departments = getDepartments();
    $applications = [];
    
    // Obtener todas las aplicaciones para admin
    $query = $db->query('SELECT a.*, d.name as department_name 
                        FROM applications a 
                        JOIN departments d ON a.department_id = d.id 
                        ORDER BY d.name, a.name');
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $applications[] = $row;
    }
} else {
    // Para usuarios normales, solo su departamento
    $departmentId = $user['department_id'];
    $stmt = $db->prepare('SELECT * FROM departments WHERE id = :id');
    $stmt->bindValue(':id', $departmentId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $departments = [$result->fetchArray(SQLITE3_ASSOC)];
    
    $applications = getApplicationsByDepartment($departmentId);
}
?>

<div class="dashboard-header">
    <h2>Panel de control</h2>
    
    <?php if ($isAdmin): ?>
        <a href="departments.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo departamento
        </a>
    <?php endif; ?>
</div>

<div class="dashboard-content">
    <?php if ($isAdmin): ?>
        <div class="stats">
            <div class="stat-card">
                <h3>Departamentos</h3>
                <p><?= count($departments) ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Aplicaciones</h3>
                <p><?= count($applications) ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Usuarios</h3>
                <?php
                $result = $db->query('SELECT COUNT(*) as count FROM users');
                $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
                ?>
                <p><?= $count ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="applications-list">
        <?php foreach ($departments as $department): ?>
            <div class="department-section">
                <h3>
                    <i class="fas fa-building"></i> <?= htmlspecialchars($department['name']) ?>
                    <?php if ($isAdmin): ?>
                        <a href="departments.php?action=edit&id=<?= $department['id'] ?>" class="btn btn-small">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    <?php endif; ?>
                </h3>
                
                <?php if ($isAdmin): ?>
                    <a href="departments.php?action=add_app&dept_id=<?= $department['id'] ?>" class="btn btn-small">
                        <i class="fas fa-plus"></i> Agregar aplicación
                    </a>
                <?php endif; ?>
                
                <?php 
                $deptApps = array_filter($applications, function($app) use ($department) {
                    return $app['department_id'] == $department['id'];
                });
                
                if (empty($deptApps)): ?>
                    <p>No hay aplicaciones registradas en este departamento.</p>
                <?php else: ?>
                    <div class="apps-grid">
                        <?php foreach ($deptApps as $app): ?>
                            <div class="app-card">
                                <div class="app-header">
                                    <h4><?= htmlspecialchars($app['name']) ?></h4>
                                    <?php if ($isAdmin): ?>
                                        <div class="app-actions">
                                            <a href="departments.php?action=edit_app&id=<?= $app['id'] ?>" class="btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                             </a>
                                            <a href="#" class="btn-icon delete-app" data-id="<?= $app['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="app-details">
                                    <?php if (!empty($app['url'])): ?>
                                        <p><i class="fas fa-link"></i> <a href="<?= htmlspecialchars($app['url']) ?>" target="_blank"><?= htmlspecialchars($app['url']) ?></a></p>
                                    <?php endif; ?>
                                    
                                    <p><i class="fas fa-user"></i> <?= htmlspecialchars($app['username']) ?></p>
                                    
                                    <div class="password-field">
                                        <input type="password" value="<?= decryptPassword($app['password']) ?>" readonly class="password-input">
                                        <button class="btn-icon toggle-password"><i class="fas fa-eye"></i></button>
                                        <button class="btn-icon copy-password" data-password="<?= decryptPassword($app['password']) ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    
                                    <?php if (!empty($app['notes'])): ?>
                                        <p class="app-notes"><i class="fas fa-sticky-note"></i> <?= htmlspecialchars($app['notes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>