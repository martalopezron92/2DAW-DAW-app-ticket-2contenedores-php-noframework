<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

// Si el usuario está autenticado, redirigir a tickets
if (is_logged_in()) {
    redirect('tickets.php');
}

// Si no está autenticado, redirigir a login
redirect('login.php');
