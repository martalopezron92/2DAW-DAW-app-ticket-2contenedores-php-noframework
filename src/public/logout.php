<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

// Cerrar sesión
logout_user();

// Redirigir a login
redirect('login.php');
