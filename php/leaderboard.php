<?php
require "db.php";
require "Session.php";
require "Score.php";

Session::start();
header("Content-Type: application/json; charset=UTF-8");

$isLoggedIn = Session::isLoggedIn();
$scoreManager = new Score($conn);
$data = $scoreManager->getLeaderboard();

echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'data' => $data ?? []
]);
?>
