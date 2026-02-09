<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Proteger página: solo usuarios autenticados
require_login();

$user = get_logged_user();
$db = Database::getInstance();

// Obtener filtro de estado
$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'open', 'closed'];
if (!in_array($statusFilter, $allowedStatuses)) {
    $statusFilter = 'all';
}

// Construir consulta según filtro
if ($statusFilter === 'all') {
    $tickets = $db->query(
        'SELECT t.*, u.name as creator_name 
         FROM tickets t 
         INNER JOIN users u ON t.created_by = u.id 
         ORDER BY t.created_at DESC'
    );
} else {
    $tickets = $db->query(
        'SELECT t.*, u.name as creator_name 
         FROM tickets t 
         INNER JOIN users u ON t.created_by = u.id 
         WHERE t.status = ?
         ORDER BY t.created_at DESC',
        [$statusFilter]
    );
}

// Contar tickets por estado
$stats = $db->queryOne(
    'SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_count,
        SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed_count
     FROM tickets'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tickets - Sistema de Tickets</title>
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>🎫 Sistema de Tickets</h1>
            <div class="user-info">
                <span>Hola, <?= h($user['name']) ?></span>
                <a href="<?= url('logout.php') ?>" class="btn btn-small">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="page-header">
            <h2>Lista de Tickets</h2>
            <a href="<?= url('ticket_new.php') ?>" class="btn btn-primary">+ Nuevo Ticket</a>
        </div>
        
        <!-- Filtros -->
        <div class="filters">
            <div class="tabs">
                <a href="?status=all" class="tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
                    Todos (<?= $stats['total'] ?>)
                </a>
                <a href="?status=open" class="tab <?= $statusFilter === 'open' ? 'active' : '' ?>">
                    Abiertos (<?= $stats['open_count'] ?>)
                </a>
                <a href="?status=closed" class="tab <?= $statusFilter === 'closed' ? 'active' : '' ?>">
                    Cerrados (<?= $stats['closed_count'] ?>)
                </a>
            </div>
        </div>
        
        <!-- Tabla de tickets -->
        <?php if (empty($tickets)): ?>
            <div class="empty-state">
                <p>No hay tickets para mostrar.</p>
                <a href="<?= url('ticket_new.php') ?>" class="btn btn-primary">Crear el primer ticket</a>
            </div>
        <?php else: ?>
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Creado por</th>
                        <th>Fecha creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>#<?= h((string)$ticket['id']) ?></td>
                            <td>
                                <a href="<?= url('ticket_view.php?id=' . $ticket['id']) ?>" class="ticket-title">
                                    <?= h($ticket['title']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-<?= $ticket['status'] ?>">
                                    <?= $ticket['status'] === 'open' ? 'Abierto' : 'Cerrado' ?>
                                </span>
                            </td>
                            <td><?= h($ticket['creator_name']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></td>
                            <td>
                                <a href="<?= url('ticket_view.php?id=' . $ticket['id']) ?>" class="btn btn-small">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    
    <script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
