<?php
session_start();
require_once "includes/conexao.php";

$codigo = strtoupper(trim($_POST['codigo']));

$stmt = $conn->prepare("SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 AND data_validade >= CURDATE()");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['cupom'] = $codigo;
    header("Location: carrinho.php?cupom=ok");
} else {
    header("Location: carrinho.php?cupom=invalido");
}
exit;
