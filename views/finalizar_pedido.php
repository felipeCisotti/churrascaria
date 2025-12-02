<?php
session_start();
require_once "../includes/connect.php";


error_log("=== INICIANDO FINALIZAR PEDIDO ===");

if (!isset($_SESSION['id'])) {
    error_log("UsuÃ¡rio nÃ£o logado - redirecionando para login");
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$endereco_entrega_id = $_POST['endereco_entrega_id'] ?? null;
$observacoes = $_POST['observacoes'] ?? '';

error_log("Dados recebidos:");
error_log("UsuÃ¡rio ID: " . $usuario_id);
error_log("EndereÃ§o ID: " . ($endereco_entrega_id ?: 'NULL'));
error_log("ObservaÃ§Ãµes: " . $observacoes);


if (!$endereco_entrega_id) {
    error_log("ERRO: EndereÃ§o nÃ£o selecionado");
    $_SESSION['erro_pedido'] = "Selecione um endereÃ§o de entrega";
    header("Location: carrinho.php");
    exit;
}


try {
    $sqlEndereco = "SELECT * FROM enderecos WHERE id = ? AND usuario_id = ?";
    $stmtEndereco = $pdo->prepare($sqlEndereco);
    $stmtEndereco->execute([$endereco_entrega_id, $usuario_id]);
    $endereco = $stmtEndereco->fetch();

    if (!$endereco) {
        error_log("ERRO: EndereÃ§o invÃ¡lido - ID: $endereco_entrega_id, UsuÃ¡rio: $usuario_id");
        $_SESSION['erro_pedido'] = "EndereÃ§o invÃ¡lido";
        header("Location: carrinho.php");
        exit;
    }
    
    error_log("EndereÃ§o vÃ¡lido: " . $endereco['titulo']);
} catch (Exception $e) {
    error_log("ERRO ao verificar endereÃ§o: " . $e->getMessage());
    $_SESSION['erro_pedido'] = "Erro ao verificar endereÃ§o";
    header("Location: carrinho.php");
    exit;
}


if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    error_log("ERRO: Carrinho vazio");
    $_SESSION['erro_pedido'] = "Seu carrinho estÃ¡ vazio";
    header("Location: carrinho.php");
    exit;
}

error_log("Itens no carrinho: " . count($_SESSION['carrinho']));

$taxa_entrega = 8.00;
$subtotal = 0;


$ids = array_keys($_SESSION['carrinho']);

try {
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT id, preco FROM produtos WHERE id IN ($placeholders) AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    
    while ($row = $stmt->fetch()) {
        $qtd = $_SESSION['carrinho'][$row['id']];
        $subtotal += $row['preco'] * $qtd;
        error_log("Produto {$row['id']}: {$row['preco']} x $qtd");
    }
} catch (Exception $e) {
    error_log("ERRO ao buscar produtos: " . $e->getMessage());
    $_SESSION['erro_pedido'] = "Erro ao processar produtos do carrinho";
    header("Location: carrinho.php");
    exit;
}

error_log("Subtotal: $subtotal");

$total = $subtotal + $taxa_entrega;
error_log("Total com taxa: $total");


if (isset($_SESSION['cupom'])) {
    $cupom = $_SESSION['cupom'];
    error_log("Verificando cupom: $cupom");
    
    try {
        $sqlCupom = "SELECT desconto FROM cupons WHERE codigo = ? AND ativo = 1 AND data_validade >= CURDATE()";
        $stmtCupom = $pdo->prepare($sqlCupom);
        $stmtCupom->execute([$cupom]);
        $cupomData = $stmtCupom->fetch();
        
        if ($cupomData) {
            $desconto = $cupomData['desconto'];
            $total = $total - ($total * ($desconto / 100));
            error_log("Cupom aplicado: $desconto%, Novo total: $total");
        } else {
            error_log("Cupom invÃ¡lido ou expirado");
        }
    } catch (Exception $e) {
        error_log("ERRO ao verificar cupom: " . $e->getMessage());
        
    }
}


try {
    $pdo->beginTransaction();
    
    
    $sqlCheckColumns = "SHOW COLUMNS FROM pedidos";
    $stmtCheck = $pdo->query($sqlCheckColumns);
    $columns = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
    error_log("Colunas da tabela pedidos: " . implode(', ', $columns));
    
    
    if (in_array('observacoes', $columns)) {
        
        $sqlPedido = "INSERT INTO pedidos (usuario_id, total, status, endereco_entrega_id, observacoes) VALUES (?, ?, 'pendente', ?, ?)";
        $stmtPedido = $pdo->prepare($sqlPedido);
        $stmtPedido->execute([$usuario_id, $total, $endereco_entrega_id, $observacoes]);
    } else {
        
        $sqlPedido = "INSERT INTO pedidos (usuario_id, total, status, endereco_entrega_id) VALUES (?, ?, 'pendente', ?)";
        $stmtPedido = $pdo->prepare($sqlPedido);
        $stmtPedido->execute([$usuario_id, $total, $endereco_entrega_id]);
    }
    
    $pedido_id = $pdo->lastInsertId();
    error_log("Pedido criado com ID: $pedido_id");
    
    
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        try {
            $sqlProduto = "SELECT preco FROM produtos WHERE id = ?";
            $stmtProduto = $pdo->prepare($sqlProduto);
            $stmtProduto->execute([$id]);
            
            if ($produto = $stmtProduto->fetch()) {
                $sqlItem = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
                $stmtItem = $pdo->prepare($sqlItem);
                $stmtItem->execute([$pedido_id, $id, $qtd, $produto['preco']]);
                error_log("Item adicionado: Produto $id, Quantidade $qtd");
            } else {
                error_log("ERRO: Produto $id nÃ£o encontrado");
                throw new Exception("Produto $id nÃ£o encontrado");
            }
        } catch (Exception $e) {
            error_log("ERRO ao inserir item $id: " . $e->getMessage());
            throw $e;
        }
    }
    
    $pdo->commit();
    error_log("Pedido finalizado com sucesso! ID: $pedido_id");
    
    
    unset($_SESSION['carrinho']);
    unset($_SESSION['cupom']);
    
    
    header("Location: confirmacao.php?pedido=$pedido_id");
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("ERRO ao processar pedido: " . $e->getMessage());
    $_SESSION['erro_pedido'] = "Erro ao processar pedido: " . $e->getMessage();
    header("Location: carrinho.php");
    exit;
}
?>