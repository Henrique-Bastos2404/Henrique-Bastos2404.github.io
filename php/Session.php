<?php
// Session.php - simples wrapper de sessão
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configura cookie da sessão para garantir path e SameSite (compatível localmente)
            $params = session_get_cookie_params();
            $cookieParams = [
                'lifetime' => $params['lifetime'],
                'path' => '/',
                'domain' => $params['domain'],
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            if (PHP_VERSION_ID >= 70300) {
                session_set_cookie_params($cookieParams);
            } else {
                // Fallback para versões antigas: usar a versão sem samesite
                session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
            }

            session_start();
        }
    }

    public static function setUser($user_id, $username) {
        $_SESSION['user_id'] = (int) $user_id;
        $_SESSION['username'] = $username;
    }

    public static function logout() {
        // Limpa a sessão e destrói cookie
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        @session_destroy();
    }

    public static function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }

    public static function getUserId() {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function getUsername() {
        return isset($_SESSION['username']) ? $_SESSION['username'] : null;
    }
}
?>