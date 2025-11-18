<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../views/login.php");
    exit();
}

$id = $_SESSION['id'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];

if (!empty($senha)) {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $senhaHash, $id]);
} else {
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $id]);
}

header("Location: ../views/perfil.php?sucesso=1");
exit();
