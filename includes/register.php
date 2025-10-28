<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $tipo = "cliente"; // Force 'cliente' type for registration

    // Hash the password
    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    // Prepare an insert statement
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";

    if ($stmt = $connect->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssss", $param_nome, $param_email, $param_senha, $param_tipo);

        // Set parameters
        $param_nome = $nome;
        $param_email = $email;
        $param_senha = $hashed_password;
        $param_tipo = $tipo;

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Redirect to login page
            header("location: ../views/login.html");
        } else {
            echo "Algo deu errado. Por favor, tente novamente mais tarde.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$connect->close();
?>
