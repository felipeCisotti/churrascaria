<?php
session_start();
require_once "../includes/connect.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$taxa_entrega = 8.00;
$subtotal = 0;

$pedido_id = $_GET['pedido'] ?? null;

if (!$pedido_id) {
    header("Location: index.php");
    exit;
}


$sqlPedido = "SELECT p.*, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep 
              FROM pedidos p 
              LEFT JOIN enderecos e ON p.endereco_entrega_id = e.id 
              WHERE p.id = ? AND p.usuario_id = ?";
$stmtPedido = $pdo->prepare($sqlPedido);
$stmtPedido->execute([$pedido_id, $_SESSION['id']]);
$pedido = $stmtPedido->fetch();

if (!$pedido) {
    header("Location: index.php");
    exit;
}


$sqlItens = "SELECT ip.*, pr.nome, pr.descricao, pr.imagem 
             FROM itens_pedido ip 
             JOIN produtos pr ON ip.produto_id = pr.id 
             WHERE ip.pedido_id = ?";
$stmtItens = $pdo->prepare($sqlItens);
$stmtItens->execute([$pedido_id]);
$itens = $stmtItens->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado | Dom Brasa</title>
    <link rel="stylesheet" href="../assets/style_carrinho.css">
    <style>

        :root{
    --marrom: #802B01;
    --escuro: #421700;
    --claro: #B85F1B;
    --vermelho: #BD1600;
    --amarelo: #DC7700;
}

        .confirmacao-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            background-color: var(--vermelho);
            margin-top: 40px;
            margin-bottom: 40px;
            border-radius: 10px;
        }
        
        .confirmacao-sucesso {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .confirmacao-icon {
            font-size: 4rem;
            color: var(--vermelho);
            margin-bottom: 20px;
        }
        
        .numero-pedido {
            background: var(--vermelho);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 1.2rem;
            font-weight: bold;
            display: inline-block;
            margin: 15px 0;
        }
        
        .detalhes-pedido {
            background: #e1e1e1ff;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            text-align: left;
            color: black;
        }
        
        .lista-itens {
            margin: 20px 0;
        }
        
        .item-pedido {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-quantidade {
            background: var(--vermelho);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-right: 15px;
        }
        
        .acoes-confirmacao {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-voltar, .btn-acompanhar {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-voltar {
            background: #6c757d;
            color: white;
        }
        
        .btn-acompanhar {
            background: var(--vermelho);
            color: white;
        }
        
        .btn-voltar:hover, .btn-acompanhar:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="confirmacao-container">
        <div class="confirmacao-sucesso">
            <div class="confirmacao-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Pedido Confirmado!</h1>
            <p>Seu pedido foi recebido e estÃ¡ sendo preparado.</p>
            
            <div class="numero-pedido">
                Pedido #<?php echo $pedido_id; ?>
            </div>
            
            <div class="detalhes-pedido">
                <h3>Detalhes do Pedido</h3>
                
                <div class="lista-itens">
                    <?php foreach ($itens as $item): ?>
                        <div class="item-pedido">
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($item['nome']); ?></strong>
                                <span class="item-quantidade"><?php echo $item['quantidade']; ?>x</span>
                            </div>
                            <div class="item-preco">
                                R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="resumo-pedido-mini">
                    <div class="resumo-item">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($pedido['total'] - $taxa_entrega, 2, ',', '.'); ?></span>
                    </div>
                    <div class="resumo-item">
                        <span>Taxa de entrega:</span>
                        <span>R$ <?php echo number_format($taxa_entrega, 2, ',', '.'); ?></span>
                    </div>
                    <div class="resumo-item total">
                        <span>Total:</span>
                        <span>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <?php if ($pedido['logradouro']): ?>
                    <div class="endereco-entrega">
                        <h4>EndereÃ§o de Entrega</h4>
                        <p><?php echo htmlspecialchars($pedido['logradouro']); ?>, <?php echo htmlspecialchars($pedido['numero']); ?></p>
                        <?php if (!empty($pedido['complemento'])): ?>
                            <p>Complemento: <?php echo htmlspecialchars($pedido['complemento']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($pedido['bairro']); ?> - <?php echo htmlspecialchars($pedido['cidade']); ?>/<?php echo htmlspecialchars($pedido['estado']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($pedido['observacoes'])): ?>
                    <div class="observacoes-pedido">
                        <h4>ObservaÃ§Ãµes</h4>
                        <p><?php echo htmlspecialchars($pedido['observacoes']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <p><strong>Tempo estimado de entrega:</strong> 40-60 minutos</p>
            
            <div class="acoes-confirmacao">
                <a href="index.php" class="btn-voltar">
                    <i class="fas fa-home"></i> Voltar ao InÃ­cio
                </a>
                <a href="perfil.php?tab=pedidos" class="btn-acompanhar">
                    <i class="fas fa-clipboard-list"></i> Acompanhar Pedido
                </a>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>