<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$endereco_id = $_POST['endereco_id'] ?? null;
$usuario_id = $_SESSION['id'];

if (!$endereco_id) {
    echo json_encode(['success' => false, 'message' => 'Endereço não especificado']);
    exit;
}

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Remover principal de todos os endereços
    $sqlUpdate = "UPDATE enderecos SET principal = 0 WHERE usuario_id = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$usuario_id]);
    
    // Definir novo endereço como principal
    $sql = "UPDATE enderecos SET principal = 1 WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$endereco_id, $usuario_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Endereço principal definido com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao definir endereço principal']);
}
?>