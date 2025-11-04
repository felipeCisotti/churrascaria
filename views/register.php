<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-log">
        <div class="login-form">
            <form action="../includes/register.php" method="post">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="tel" id="telefone" name="telefone" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo de usuário:</label>
                    <select id="tipo" name="tipo">
                        <option value="cliente" selected>Cliente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Cadastrar">
                </div>
                <div class="register-link">
                    <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
                </div>
            </form>
        </div>
        <div class="img-log">
               <img src="../assets/img/cad.png" alt="">
            </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>