<?php
declare(strict_types=1);

/**
 * Biblioteca de autenticación y gestión de sesiones
 * 
 * Proporciona funciones para login, logout, verificación de sesión
 * y protección de páginas privadas.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

/**
 * Iniciar sesión de forma segura
 */
function session_start_secure(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de sesiones
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '0'); // Cambiar a '1' si usas HTTPS
        
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerar ID de sesión periódicamente para prevenir fijación
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > SESSION_LIFETIME) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Verificar si el usuario está autenticado
 */
function is_logged_in(): bool
{
    session_start_secure();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Obtener datos del usuario actual
 */
function get_logged_user(): ?array
{
    session_start_secure();
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Middleware: requerir autenticación
 * 
 * Redirige a login si el usuario no está autenticado.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Autenticar usuario
 * 
 * @param string $email Email del usuario
 * @param string $password Contraseña en texto plano
 * @return array|null Datos del usuario si el login es exitoso, null si falla
 */
function login_user(string $email, string $password): ?array
{
    $db = Database::getInstance();
    
    // Buscar usuario por email
    $user = $db->queryOne(
        'SELECT id, name, email, password_hash, role FROM users WHERE email = ?',
        [$email]
    );
    
    if (!$user) {
        return null;
    }
    
    // Verificar contraseña
    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }
    
    // Iniciar sesión
    session_start_secure();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['created'] = time();
    
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

/**
 * Cerrar sesión del usuario
 */
function logout_user(): void
{
    session_start_secure();
    
    // Limpiar variables de sesión
    $_SESSION = [];
    
    // Destruir cookie de sesión
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destruir sesión
    session_destroy();
}

/**
 * Escapar HTML para prevenir XSS
 */
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generar URL completa
 */
function url(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirigir a una URL
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}
