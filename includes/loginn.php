<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    if ($senha === '') {
        echo "Email/Usuário ou senha inválidos.";
        exit;
    }

    // Permitir login por email (válido) ou por nome de usuário (quando email inválido)
    $loginField = null;
    $loginValue = null;
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginField = 'email';
        $loginValue = $email;
    } elseif ($nome !== '') {
        $loginField = 'nome';
        $loginValue = $nome;
    } else {
        echo "Email/Usuário ou senha inválidos.";
        exit;
    }

    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE ${loginField} = ?";

    if ($stmt = $connect->prepare($sql)) {
        $stmt->bind_param("s", $loginValue);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nome, $db_email, $stored_password, $tipo);
                if ($stmt->fetch()) {
                    $loginOk = false;

                    // Primeiro, tente verificar como hash (bcrypt/argon2)
                    if (!empty($stored_password) && password_verify($senha, $stored_password)) {
                        $loginOk = true;
                        // Opcional: rehash se o algoritmo/custo mudou
                        if (password_needs_rehash($stored_password, PASSWORD_DEFAULT)) {
                            if ($update = $connect->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")) {
                                $newHash = password_hash($senha, PASSWORD_DEFAULT);
                                $update->bind_param("si", $newHash, $id);
                                $update->execute();
                                $update->close();
                            }
                        }
                    } else {
                        // Fallback: se a senha armazenada não parece um hash, comparar texto puro
                        $looksHashed = (substr($stored_password, 0, 4) === '$2y$') || (substr($stored_password, 0, 8) === '$argon2');
                        if (!$looksHashed && hash_equals((string)$stored_password, (string)$senha)) {
                            $loginOk = true;
                            // Ao logar com senha em texto, atualiza para hash seguro
                            if ($update = $connect->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")) {
                                $newHash = password_hash($senha, PASSWORD_DEFAULT);
                                $update->bind_param("si", $newHash, $id);
                                $update->execute();
                                $update->close();
                            }
                        }
                    }

                    if ($loginOk) {
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
                            header("Location: ../views/cadastrado.php");
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
