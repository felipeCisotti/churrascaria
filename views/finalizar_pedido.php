<?php
session_start();
require_once "includes/conexao.php";

if (!isset($_SESSION['id'])) {
    header("Location: views/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$taxa_entrega = 8.00;
$subtotal = 0;

foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $sql = "SELECT preco FROM produtos WHERE id = $id";
    $res = $conn->query($sql);
    if ($row = $res->fetch_assoc()) {
        $subtotal += $row['preco'] * $qtd;
    }
}

$total = $subtotal + $taxa_entrega;

// aplicar cupom
if (isset($_SESSION['cupom'])) {
    $cupom = $_SESSION['cupom'];
    $stmt = $conn->prepare("SELECT desconto FROM cupons WHERE codigo = ? AND ativo = 1 AND data_validade >= CURDATE()");
    $stmt->bind_param("s", $cupom);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
        $total = $total - ($total * ($r['desconto'] / 100));
    }
}

// cria o pedido
$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, status, endereco_entrega) VALUES (?, ?, 'pendente', 'Endereço padrão')");
$stmt->bind_param("id", $usuario_id, $total);
$stmt->execute();
$pedido_id = $stmt->insert_id;

// adiciona itens
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $sql = "SELECT preco FROM produtos WHERE id = $id";
    $res = $conn->query($sql);
    if ($row = $res->fetch_assoc()) {
        $preco = $row['preco'];
        $stmt = $conn->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $pedido_id, $id, $qtd, $preco);
        $stmt->execute();
    }
}

unset($_SESSION['carrinho']);
unset($_SESSION['cupom']);

header("Location: confirmacao.php?pedido=$pedido_id");
exit;
