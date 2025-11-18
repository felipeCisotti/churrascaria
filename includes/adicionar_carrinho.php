<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "not_logged";
    exit;
}

if (isset($_POST['produto_id']) && isset($_POST['quantidade'])) {
    $produto_id = (int)$_POST['produto_id'];
    $quantidade = (int)$_POST['quantidade'];
    
    // Verificar se o produto existe e está ativo
    $sql = "SELECT * FROM produtos WHERE id = ? AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        // Inicializar carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
        
        // Adicionar ou atualizar produto no carrinho
        if (isset($_SESSION['carrinho'][$produto_id])) {
            $_SESSION['carrinho'][$produto_id] += $quantidade;
        } else {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        }
        
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>