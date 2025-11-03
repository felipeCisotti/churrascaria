<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        echo "Email ou senha inválidos.";
        exit;
    }

    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";

    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nome, $db_email, $hashed_password, $tipo);
                if ($stmt->fetch()) {
                    if (password_verify($senha, $hashed_password)) {
                        session_regenerate_id(true);
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["nome"] = $nome;
                        $_SESSION["email"] = $db_email;
                        $_SESSION["tipo"] = $tipo;
                        if ($tipo === "admin") {
                            header("Location: ../dashboard.php");
                            exit;
                        } else {
                            header("Location: ../index.php");
                            exit;
                        }
                    } else {
                        echo "A senha que você digitou não é válida.";
                    }
                }
            } else {
                echo "Nenhuma conta encontrada com esse email.";
            }
        } else {
            error_log("Login execute error: " . $stmt->error);
            echo "Oops! Algo deu errado. Por favor, tente novamente mais tarde.";
        }

        $stmt->close();
    } else {
        error_log("Login prepare error: " . $connect->error);
        echo "Erro no servidor.";
    }
}

$connect->close();
?>
