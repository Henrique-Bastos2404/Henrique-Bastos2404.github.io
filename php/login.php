<?php
require "db.php";
require "Session.php";
require "Player.php";

Session::start();
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Ação de logout
    if (isset($data['action']) && $data['action'] === 'logout') {
        Session::logout();
        echo json_encode(['success' => true, 'message' => 'Logout realizado']);
        exit();
    }
    
    // Login normal
    $username = isset($data['username']) ? $data['username'] : '';
    $password = isset($data['password']) ? $data['password'] : '';
    
    $player = new Player($conn);
    $result = $player->login($username, $password);
    
    if ($result['success']) {
        Session::setUser($result['user_id'], $result['username']);
        // Regenerate session id to ensure fresh session cookie
        if (function_exists('session_regenerate_id')) session_regenerate_id(true);
    }
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>
