<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FICAEMCASA - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>


    <div class="container-log">

                    <div class="login-form">

                        <form action="../includes/loginn.php" method="post">

                            <div class="form-group">
                                <label for="nome">Login ou E-mail</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>
                    
                            <div class="form-group">
                                <label for="senha">Senha</label>
                                <input type="password" id="senha" name="senha" required>
                            </div>
                    
                            <div class="forgot-password">
                                <a href="#">Esqueceu a Senha?</a>
                            </div>
                    
                            <div class="form-group">
                                <input type="submit" value="ENTRAR">
                            </div>
                    
                            <div class="register-link">
                                Não possui login? <a href="register.php">Cadastre-se</a>
                            </div>
                        </form>
                    </div>

            <div class="img-log">
               <img src="../assets/img/log.png" alt="">
            </div>

    </div>
</body>
</html>

<?php include'../includes/footer.php'; ?>
