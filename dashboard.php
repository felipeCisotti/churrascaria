<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: views/login.html");
    exit;
}

// Check if the user is an admin, if not then redirect to index page
if ($_SESSION["tipo"] !== "admin") {
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="welcome-container">
        <h2>Bem-vindo ao Dashboard Admin, <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</h2>
        <p>Você está logado como: <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
        <p>Tipo de usuário: <?php echo htmlspecialchars($_SESSION["tipo"]); ?></p>
        <p>
            <a href="includes/logout.php" class="btn">Sair da conta</a>
        </p>
        <!-- Admin specific content can go here -->
        <h3>Gerenciamento de Usuários</h3>
        <p>Aqui você pode gerenciar usuários, produtos, etc.</p>
    </div>
</body>
</html>
