<?php
// db.php - wrapper que inicializa $conn a partir de config.php
require_once __DIR__ . '/config.php';

// Verifica se $conn foi definido corretamente
if (!isset($conn) || !$conn) {
    http_response_code(500);
    error_log('db.php: $conn não definido. Verifique config.php');
    die(json_encode(['success' => false, 'message' => 'Erro na conexão à base de dados']));
}
?>