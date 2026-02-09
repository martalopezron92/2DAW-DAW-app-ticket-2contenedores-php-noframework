<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Proteger página: solo usuarios autenticados
require_login();

$user = get_logged_user();
$db = Database::getInstance();

$error = null;
$success = null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validación
    if (empty($title)) {
        $error = 'El título es obligatorio.';
    } elseif (strlen($title) > 255) {
        $error = 'El título no puede superar los 255 caracteres.';
    } elseif (empty($description)) {
        $error = 'La descripción es obligatoria.';
    } else {
        try {
            // Insertar ticket
            $ticketId = $db->execute(
                'INSERT INTO tickets (title, description, created_by) VALUES (?, ?, ?)',
                [$title, $description, $user['id']]
            );
            
            // Redirigir al ticket creado
            redirect('ticket_view.php?id=' . $ticketId);
        } catch (Exception $e) {
            $error = 'Error al crear el ticket. Por favor, inténtalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket - Sistema de Tickets</title>
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
            <h2>Crear Nuevo Ticket</h2>
            <a href="<?= url('tickets.php') ?>" class="btn btn-secondary">← Volver a tickets</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= h($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="ticket-form">
            <div class="form-group">
                <label for="title">Título del ticket: *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required 
                    maxlength="255"
                    autofocus
                    value="<?= h($_POST['title'] ?? '') ?>"
                    placeholder="Ej: Error al enviar formulario de contacto"
                >
                <small>Máximo 255 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="description">Descripción: *</label>
                <textarea 
                    id="description" 
                    name="description" 
                    required 
                    rows="8"
                    placeholder="Describe el problema o solicitud con el mayor detalle posible..."
                ><?= h($_POST['description'] ?? '') ?></textarea>
                <small>Proporciona todos los detalles relevantes</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Crear Ticket</button>
                <a href="<?= url('tickets.php') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
    
    <script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>
