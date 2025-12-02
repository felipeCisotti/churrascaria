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
    $sql = "DELETE FROM enderecos WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$endereco_id, $usuario_id]);
    
    echo json_encode(['success' => true, 'message' => 'EndereÃ§o excluÃ­do com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir endereÃ§o']);
}
?>