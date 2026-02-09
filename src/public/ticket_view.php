<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Proteger página: solo usuarios autenticados
require_login();

$user = get_logged_user();
$db = Database::getInstance();

// Obtener ID del ticket
$ticketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ticketId) {
    redirect('tickets.php');
}

// Obtener datos del ticket
$ticket = $db->queryOne(
    'SELECT t.*, u.name as creator_name, u.email as creator_email
     FROM tickets t 
     INNER JOIN users u ON t.created_by = u.id 
     WHERE t.id = ?',
    [$ticketId]
);

if (!$ticket) {
    redirect('tickets.php');
}

$canClose = $ticket['status'] === 'open';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= h((string)$ticket['id']) ?> - Sistema de Tickets</title>
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
            <h2>Ticket #<?= h((string)$ticket['id']) ?></h2>
            <a href="<?= url('tickets.php') ?>" class="btn btn-secondary">← Volver a tickets</a>
        </div>
        
        <div class="ticket-detail">
            <div class="ticket-header">
                <h3><?= h($ticket['title']) ?></h3>
                <span class="badge badge-<?= $ticket['status'] ?> badge-large">
                    <?= $ticket['status'] === 'open' ? 'Abierto' : 'Cerrado' ?>
                </span>
            </div>
            
            <div class="ticket-meta">
                <div class="meta-item">
                    <strong>Creado por:</strong>
                    <?= h($ticket['creator_name']) ?> (<?= h($ticket['creator_email']) ?>)
                </div>
                <div class="meta-item">
                    <strong>Fecha de creación:</strong>
                    <?= date('d/m/Y H:i:s', strtotime($ticket['created_at'])) ?>
                </div>
                <?php if ($ticket['closed_at']): ?>
                    <div class="meta-item">
                        <strong>Fecha de cierre:</strong>
                        <?= date('d/m/Y H:i:s', strtotime($ticket['closed_at'])) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ticket-description">
                <h4>Descripción:</h4>
                <div class="description-content">
                    <?= nl2br(h($ticket['description'])) ?>
                </div>
            </div>
            
            <?php if ($canClose): ?>
                <div class="ticket-actions">
                    <form method="POST" action="<?= url('ticket_close.php') ?>" onsubmit="return confirm('¿Estás seguro de que deseas cerrar este ticket?');">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <button type="submit" class="btn btn-warning">Cerrar Ticket</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Este ticket está cerrado.
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
