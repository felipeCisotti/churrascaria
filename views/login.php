<?php include '../includes/header.php'; ?>


    <div class="container-log">

                    <div class="login-form">

                        <form action="../includes/loginn.php" method="post">

                            <div class="form-group">
                                <label for="nome">Login</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" required>
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
