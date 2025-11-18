<?php
session_start();

$total_itens = 0;
if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $total_itens = array_sum($_SESSION['carrinho']);
}

header('Content-Type: application/json');
echo json_encode(['total_itens' => $total_itens]);
?>