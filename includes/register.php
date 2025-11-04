<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';
    $telefone = trim($_POST["telefone"] ?? '');
    // aceita apenas 'admin' ou 'cliente' (validação server-side)
    $tipo_post = $_POST['tipo'] ?? 'cliente';
    $tipo = ($tipo_post === 'admin') ? 'admin' : 'cliente';

    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, telefone) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("sssss", $param_nome, $param_email, $param_senha, $param_tipo, $param_telefone);

        $param_nome = $nome;
        $param_email = $email;
        $param_senha = $hashed_password;
        $param_tipo = $tipo;
        $param_telefone = $telefone;

        if ($stmt->execute()) {
            header("location: ../views/login.php");
            exit;
        } else {
            echo "Algo deu errado. Por favor, tente novamente mais tarde.";
        }

        $stmt->close();
    }
}

$connect->close();
?>
