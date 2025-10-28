<?php

session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";

    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("s", $param_email);

        $param_email = $email;

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nome, $email, $hashed_password, $tipo);
                if ($stmt->fetch()) {
                    if (password_verify($senha, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["nome"] = $nome;
                        $_SESSION["email"] = $email;
                        $_SESSION["tipo"] = $tipo;
                        // Redirect user based on their type
                        if ($_SESSION["tipo"] == "admin") {
                            header("location: ../dashboard.php");
                        } else {
                            header("location: ../index.php");
                        }
                    } else {
                        echo "A senha que você digitou não é válida.";
                    }
                }
            } else {
                echo "Nenhuma conta encontrada com esse email.";
            }
        } else {
            echo "Oops! Algo deu errado. Por favor, tente novamente mais tarde.";
        }

        $stmt->close();
    }
}

$connect->close();
?>
