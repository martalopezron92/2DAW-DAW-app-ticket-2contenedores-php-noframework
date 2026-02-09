<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

// Proteger página: solo usuarios autenticados
require_login();

$user = get_logged_user();
$db = Database::getInstance();

// Solo procesar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tickets.php');
}

// Obtener ID del ticket
$ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);

if (!$ticketId) {
    redirect('tickets.php');
}

try {
    // Verificar que el ticket existe y está abierto
    $ticket = $db->queryOne(
        'SELECT id, status FROM tickets WHERE id = ?',
        [$ticketId]
    );
    
    if (!$ticket) {
        redirect('tickets.php');
    }
    
    if ($ticket['status'] !== 'open') {
        redirect('ticket_view.php?id=' . $ticketId);
    }
    
    // Cerrar el ticket
    $db->execute(
        'UPDATE tickets SET status = ?, closed_at = NOW() WHERE id = ?',
        ['closed', $ticketId]
    );
    
    // Redirigir al detalle del ticket
    redirect('ticket_view.php?id=' . $ticketId);
    
} catch (Exception $e) {
    error_log('Error al cerrar ticket: ' . $e->getMessage());
    redirect('tickets.php');
}
