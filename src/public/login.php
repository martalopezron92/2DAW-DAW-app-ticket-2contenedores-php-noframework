<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

// Si ya está autenticado, redirigir a tickets
if (is_logged_in()) {
    redirect('tickets.php');
}

$error = null;

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, introduce email y contraseña.';
    } else {
        $user = login_user($email, $password);
        
        if ($user) {
            // Login exitoso
            redirect('tickets.php');
        } else {
            $error = 'Credenciales incorrectas. Por favor, inténtalo de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Tickets</title>
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🎫 Sistema de Tickets</h1>
            <h2>Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autofocus 
                        value="<?= h($_POST['email'] ?? '') ?>"
                        placeholder="admin@empresa.com"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="••••••••"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="demo-credentials">
                <strong>Credenciales de prueba:</strong><br>
                Email: admin@empresa.com<br>
                Contraseña: admin1234
            </div>
        </div>
    </div>
</body>
</html>
