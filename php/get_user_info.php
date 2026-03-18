<?php
require 'db.php';
require 'Session.php';
require 'Player.php';

Session::start();
header('Content-Type: application/json; charset=UTF-8');

if (!Session::isLoggedIn()) {
    echo json_encode([]);
    exit();
}

$player = new Player($conn);
$user = $player->getById(Session::getUserId());

if (!$user) {
    echo json_encode([]);
    exit();
}

// Normalizar alguns campos esperados pelo frontend
$user['is_public'] = isset($user['is_public']) ? (bool)$user['is_public'] : true;
$user['profile_image'] = isset($user['profile_image']) ? $user['profile_image'] : null;

echo json_encode($user);
?>