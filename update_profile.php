<?php
require 'db.php';
require 'Session.php';
require 'Player.php';

Session::start();
header('Content-Type: application/json; charset=UTF-8');

if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit();
}

$player = new Player($conn);
$userId = Session::getUserId();

switch ($data['action']) {
    case 'update_username':
        $new = isset($data['username']) ? trim($data['username']) : '';
        $res = $player->updateUsername($userId, $new);
        if ($res['success']) {
            // Atualiza sessão com novo username
            Session::setUser($userId, $res['username']);
        }
        echo json_encode($res);
        break;

    case 'update_password':
        $old = isset($data['old_password']) ? $data['old_password'] : '';
        $newpw = isset($data['new_password']) ? $data['new_password'] : '';
        $conf = isset($data['confirm_password']) ? $data['confirm_password'] : '';
        $res = $player->updatePassword($userId, $old, $newpw, $conf);
        echo json_encode($res);
        break;

    case 'update_avatar':
        $avatar = isset($data['avatar']) ? $data['avatar'] : '';
        $res = $player->updateAvatar($userId, $avatar);
        echo json_encode($res);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação desconhecida']);
}

?>
