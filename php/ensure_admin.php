<?php
// Script de conveniência: cria ou atualiza a conta `admin` com uma password fornecida (por defeito: 123).
// APAGUE este ficheiro depois de usar — deixa o site inseguro se mantido em produção.

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sem ligação à BD']);
    exit();
}

$username = 'admin';
$password = isset($_GET['pw']) ? $_GET['pw'] : '123';

// Verifica se já existe
$stmt = $conn->prepare('SELECT id FROM utilizadores WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $id = (int) $row['id'];
    $stmt->close();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $up = $conn->prepare('UPDATE utilizadores SET `password` = ? WHERE id = ?');
    $up->bind_param('si', $hash, $id);
    $ok = $up->execute();
    $err = $up->error;
    $up->close();

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Password atualizada para utilizador existente', 'username' => $username]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao actualizar password', 'error' => $err]);
    }

    exit();
}

$stmt->close();

// Não existe: insere novo utilizador com tipo_id = 1 (Administrador)
$email = $username . '@local.invalid';
$tipo_id = 1;
$foto_perfil = '';
$hash = password_hash($password, PASSWORD_BCRYPT);

$ins = $conn->prepare('INSERT INTO utilizadores (ativo, tipo_id, email, `password`, username, foto_perfil) VALUES (TRUE, ?, ?, ?, ?, ?)');
$ins->bind_param('issss', $tipo_id, $email, $hash, $username, $foto_perfil);

if ($ins->execute()) {
    echo json_encode(['success' => true, 'message' => 'Conta criada', 'username' => $username]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar conta', 'error' => $ins->error]);
}

$ins->close();

?>
