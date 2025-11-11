<?php
session_start();
require_once "../includes/connect.php";

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: views/login.html");
    exit;
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Aplicar cupom, se existir
$cupom_aplicado = $_SESSION['cupom'] ?? null;
$desconto = 0;

if ($cupom_aplicado) {
    $stmt = $conn->prepare("SELECT desconto FROM cupons WHERE codigo = ? AND ativo = 1 AND data_validade >= CURDATE()");
    $stmt->bind_param("s", $cupom_aplicado);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $desconto = $result['desconto'];
    }
}

$taxa_entrega = 8.00;
$subtotal = 0;

// Busca informações dos produtos do carrinho
$produtos = [];
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $sql = "SELECT * FROM produtos WHERE id = $id";
    $res = $conn->query($sql);
    if ($row = $res->fetch_assoc()) {
        $row['quantidade'] = $qtd;
        $row['subtotal'] = $row['preco'] * $qtd;
        $subtotal += $row['subtotal'];
        $produtos[] = $row;
    }
}

$total = $subtotal + $taxa_entrega;
if ($desconto > 0) {
    $total = $total - ($total * ($desconto / 100));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/style_carrinho.css">
    <title>Carrinho | Dom Brasa</title>
</head>
<body>
<div class="container">
    <h2>SEU CARRINHO</h2>

    <div class="carrinho">
        <div class="itens">
            <?php foreach ($produtos as $p): ?>
                <div class="item">
                    <img src="<?= $p['imagem'] ?>" alt="<?= $p['nome'] ?>">
                    <div class="info">
                        <h3><?= $p['nome'] ?></h3>
                        <p><?= $p['descricao'] ?></p>
                        <p>R$<?= number_format($p['preco'], 2, ',', '.') ?></p>
                        <div class="qtd">
                            <span>Qtd: <?= $p['quantidade'] ?></span>
                            <a href="remover_carrinho.php?id=<?= $p['id'] ?>">Remover</a>
                        </div>
                        <strong>Subtotal: R$<?= number_format($p['subtotal'], 2, ',', '.') ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="resumo">
            <h3>RESUMO DO PEDIDO</h3>
            <p>Subtotal: <strong>R$<?= number_format($subtotal, 2, ',', '.') ?></strong></p>
            <p>Taxa de entrega: <strong>R$<?= number_format($taxa_entrega, 2, ',', '.') ?></strong></p>

            <form action="aplicar_cupom.php" method="post">
                <input type="text" name="codigo" placeholder="Código do cupom" required>
                <button type="submit">Aplicar</button>
            </form>

            <?php if ($cupom_aplicado): ?>
                <p>Cupom aplicado: <strong><?= htmlspecialchars($cupom_aplicado) ?></strong> (-<?= $desconto ?>%)</p>
            <?php endif; ?>

            <p>Total: <strong>R$<?= number_format($total, 2, ',', '.') ?></strong></p>

            <form action="finalizar_pedido.php" method="post">
                <button type="submit">Finalizar Pedido</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
