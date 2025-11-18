<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $produto_id = (int)$_GET['id'];
    
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
    }
}

header("Location: carrinho.php");
exit;
?>