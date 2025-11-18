<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$endereco_id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['id'];

if (!$endereco_id) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $sql = "SELECT * FROM enderecos WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$endereco_id, $usuario_id]);
    $endereco = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($endereco) {
        echo json_encode(['success' => true, 'endereco' => $endereco]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
?>