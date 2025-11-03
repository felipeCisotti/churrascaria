<?php
session_start();
include ("includes/connect.php");

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["tipo"] !== "admin") {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $sql = "SELECT id, nome, descricao, preco, categoria, imagem, ativo FROM produtos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo json_encode(['error' => 'Produto não encontrado']);
        exit;
    }

    echo json_encode($produto);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor']);
    error_log("get_produto error: " . $e->getMessage());
}
?>