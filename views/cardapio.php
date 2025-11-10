<?php
include '../includes/header.php';
include '../includes/connect.php';

// Pega as categorias existentes com produtos ativos
$sqlCategorias = "SELECT DISTINCT categoria 
                  FROM produtos 
                  WHERE ativo = 1 
                  ORDER BY categoria";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_COLUMN);
?>

<body class="cardapio-page">
    <div class="subheader">
        <img src="../assets/img/subheader.png" alt="">
    </div>

    <?php foreach ($categorias as $categoria): ?>
        <div class="cardapio-content">
            <h2><?php echo strtoupper(htmlspecialchars($categoria)); ?></h2>
            <div class="produtos-cards">
                <div class="produtos-container">

                    <?php
                    // Pega os produtos dessa categoria
                    $sqlProdutos = "SELECT * FROM produtos 
                                    WHERE categoria = :categoria 
                                    AND ativo = 1
                                    ORDER BY nome ASC";
                    $stmtProdutos = $pdo->prepare($sqlProdutos);
                    $stmtProdutos->execute(['categoria' => $categoria]);
                    $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (count($produtos) > 0): ?>
                        <?php foreach ($produtos as $produto): ?>
                            <div class="produtos">
                                <img src="../assets/img/cardapio/<?php echo htmlspecialchars($produto['imagem']); ?>"
                                     alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($produto['descricao']); ?></p>
                                <div class="card-bottom">
                                    <p>R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Nenhum produto nesta categoria.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="separador">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
        </div>
    <?php endforeach; ?>

    <script src="https://kit.fontawesome.com/03a8c367de.js" crossorigin="anonymous"></script>
</body>

<?php
include '../includes/footer.php';
?>
