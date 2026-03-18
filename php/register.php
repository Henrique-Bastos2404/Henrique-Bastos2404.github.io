<?php
require "db.php";
require "Session.php";
require "Player.php";

Session::start();
header("Content-Type: application/json; charset=UTF-8");

// Melhor tratamento de erros e validação do input JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit();
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('register.php: JSON decode error: ' . json_last_error_msg() . ' | raw: ' . $raw);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos enviados']);
    exit();
}

$username = isset($data['username']) ? $data['username'] : '';
$password = isset($data['password']) ? $data['password'] : '';
$confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : '';

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
    exit();
}

$player = new Player($conn);
$result = $player->register($username, $password, $username);

// Se registo bem-sucedido, iniciar sessão automaticamente
if (is_array($result) && isset($result['success']) && $result['success'] === true && isset($result['user_id'])) {
    Session::setUser((int)$result['user_id'], $result['username']);
    // Regenerate session id for safety
    if (function_exists('session_regenerate_id')) session_regenerate_id(true);
}

// Log detalhado em caso de falha para ajudar no diagnóstico
if (!is_array($result) || (isset($result['success']) && $result['success'] === false)) {
    $msg = is_array($result) && isset($result['message']) ? $result['message'] : 'Resultado inesperado';
    error_log('register.php: registo falhou: ' . $msg . ' | username: ' . $username);
}

echo json_encode($result);
?>
