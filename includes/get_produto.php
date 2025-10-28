<?php
session_start();
include ("includes/connect.php");

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["tipo"] !== "admin") {
    http_response_code(403);
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        header('Content-Type: application/json');
        echo json_encode($produto);
    } else {
        http_response_code(404);
    }
} else {
    http_response_code(400);
}
?>