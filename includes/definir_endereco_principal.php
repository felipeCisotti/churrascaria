<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'UsuÃ¡rio nÃ£o logado']);
    exit;
}

$endereco_id = $_POST['endereco_id'] ?? null;
$usuario_id = $_SESSION['id'];

if (!$endereco_id) {
    echo json_encode(['success' => false, 'message' => 'EndereÃ§o nÃ£o especificado']);
    exit;
}

try {
    
    $pdo->beginTransaction();
    
    
    $sqlUpdate = "UPDATE enderecos SET principal = 0 WHERE usuario_id = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$usuario_id]);
    
    
    $sql = "UPDATE enderecos SET principal = 1 WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$endereco_id, $usuario_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'EndereÃ§o principal definido com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao definir endereÃ§o principal']);
}
?>