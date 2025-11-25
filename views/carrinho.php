<?php
session_start();
require_once "../includes/connect.php";

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];


// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Processar atualização de quantidade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_carrinho'])) {
    foreach ($_POST['quantidade'] as $produto_id => $quantidade) {
        $quantidade = (int)$quantidade;
        if ($quantidade > 0) {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        } else {
            unset($_SESSION['carrinho'][$produto_id]);
        }
    }
    header("Location: carrinho.php");
    exit;
}

// Aplicar cupom, se existir
$cupom_aplicado = $_SESSION['cupom'] ?? null;
$desconto = 0;
$codigo_cupom = '';

if ($cupom_aplicado) {
    $stmt = $pdo->prepare("SELECT desconto FROM cupons WHERE codigo = ? AND ativo = 1 AND data_validade >= CURDATE()");
    $stmt->execute([$cupom_aplicado]);
    $result = $stmt->fetch();
    if ($result) {
        $desconto = $result['desconto'];
        $codigo_cupom = $cupom_aplicado;
    }
}

$taxa_entrega = 8.00;
$subtotal = 0;

// Busca informações dos produtos do carrinho
$produtos = [];
if (!empty($_SESSION['carrinho'])) {
    $ids = array_keys($_SESSION['carrinho']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $sql = "SELECT * FROM produtos WHERE id IN ($placeholders) AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    
    while ($row = $stmt->fetch()) {
        $qtd = $_SESSION['carrinho'][$row['id']];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style_carrinho.css">
    <title>Carrinho | Dom Brasa</title>

    <style>

:root{
    --marrom: #802B01;
    --escuro: #421700;
    --claro: #B85F1B;
    --vermelho: #BD1600;
    --amarelo: #DC7700;
}


.container-carrinho {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Arial', sans-serif;
    color: #333;
}

.container-carrinho h2 {
    text-align: center;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 30px;
    color: #8B0000;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Layout Principal */
.carrinho {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin-top: 20px;
}

/* Itens do Carrinho */
.itens-carrinho {
    background: white;
    border-radius: 10px;
    padding: 0;
}

.item-carrinho {
    display: flex;
    gap: 20px;
    padding: 25px;
    border-bottom: 2px solid #f0f0f0;
    background: white;
}

.item-carrinho:last-child {
    border-bottom: none;
}

.item-imagem {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-imagem img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    flex: 1;
}

.item-info h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.3rem;
    font-weight: bold;
}

.item-descricao {
    color: #666;
    margin: 0 0 15px 0;
    font-size: 0.95rem;
    line-height: 1.4;
}

.item-preco {
    font-weight: bold;
    color: var(--vermelho);
    margin: 0 0 15px 0;
    font-size: 1.1rem;
}

.item-controles {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.quantidade-controle {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantidade-controle label {
    font-weight: bold;
    color: #333;
    font-size: 0.9rem;
}

.quantidade-controle input {
    width: 60px;
    padding: 8px;
    border: 2px solid #ddd;
    border-radius: 5px;
    text-align: center;
    font-size: 1rem;
    font-weight: bold;
}

.quantidade-controle input:focus {
    border-color: var(--vermelho);
    outline: none;
}

.item-subtotal {
    font-weight: bold;
    color: #333;
    font-size: 1.1rem;
    margin-left: auto;
}

.btn-remover {
    color: var(--vermelho);
    text-decoration: none;
    padding: 8px 16px;
    border: 2px solid var(--vermelho);
    border-radius: 5px;
    font-weight: bold;
    font-size: 0.9rem;
    transition: all 0.3s;
    background: white;
    cursor: pointer;
}

.btn-remover:hover {
    background: var(--vermelho);
    color: white;
    transform: translateY(-2px);
}

/* Ações do Carrinho */
.carrinho-acoes {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

.btn-atualizar, .btn-continuar {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: bold;
    transition: all 0.3s;
    font-size: 0.95rem;
        background: var(--vermelho);
    color: white;
}


.btn-atualizar:hover, .btn-continuar:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Resumo do Pedido */
.resumo-pedido {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.resumo-pedido h3 {
    margin: 0 0 25px 0;
    color: var(--vermelho);
    text-align: center;
    font-size: 1.4rem;
    font-weight: bold;
    text-transform: uppercase;
    border-bottom: 2px solid var(--vermelho);
    padding-bottom: 10px;
}

.resumo-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
    font-size: 1rem;
}

.resumo-item.total {
    border-top: 2px solid var(--vermelho);
    border-bottom: none;
    font-size: 1.3rem;
    font-weight: bold;
    margin-top: 15px;
    padding-top: 15px;
    color: var(--vermelho);
}


/* Botão Finalizar */
.btn-finalizar {
    width: 100%;
    padding: 18px;
    background: var(--vermelho);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.2rem;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s;
    margin-top: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 100px;
}

.btn-finalizar:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

/* Seções Adicionais */
.secao-entrega, .secao-observacoes, .secao-informacoes {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-top: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.secao-entrega h4, .secao-observacoes h4, .secao-informacoes h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 1.2rem;
    font-weight: bold;
    border-bottom: 2px solid var(--vermelho);
    padding-bottom: 8px;
}

.endereco-atual {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
    border-left: 4px solid var(--vermelho) ;
}

.endereco-atual p {
    margin: 5px 0;
    color: #333;
    font-size: 1rem;
}

.btn-alterar-endereco {
    background: var(--vermelho);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-alterar-endereco:hover {
    transform: translateY(-2px);
}

.textarea-observacoes {
    width: 100%;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
}

.textarea-observacoes:focus {
    border-color: var(--vermelho);
    outline: none;
}

.textarea-observacoes::placeholder {
    color: #999;
    font-style: italic;
}

/* Informações */
.lista-informacoes {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lista-informacoes li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    color: #666;
    font-size: 0.95rem;
}

.lista-informacoes li:last-child {
    border-bottom: none;
}

.lista-informacoes strong {
    color: #333;
}

/* Carrinho Vazio */
.carrinho-vazio {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.carrinho-vazio i {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 20px;
}

.carrinho-vazio h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.5rem;
}

.carrinho-vazio p {
    color: #666;
    margin-bottom: 30px;
    font-size: 1.1rem;
}

.btn-continuar-comprando {
    background-color: var(--vermelho);
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: inline-block;
}

.btn-continuar-comprando:hover {
    background: #660000;
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

/* Responsividade */
@media (max-width: 768px) {
    .carrinho {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .item-carrinho {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .item-imagem {
        width: 100%;
        height: 200px;
    }
    
    .item-controles {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .item-subtotal {
        margin-left: 0;
    }
    
    .carrinho-acoes {
        flex-direction: column;
    }
    
    .input-cupom {
        flex-direction: column;
    }
    
    .resumo-pedido {
        position: static;
    }
    
    .container-carrinho {
        padding: 10px;
    }
    
    .container-carrinho h2 {
        font-size: 1.5rem;
    }
}

/* Estados de Loading */
.btn-finalizar.loading {
    background: #6c757d;
    cursor: not-allowed;
}

.btn-finalizar.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Preços em destaque */
.preco-destaque {
    color: var(--vermelho);
    font-weight: bold;
    font-size: 1.1rem;
}

.quantidade-destaque {
    background: #8B0000;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: bold;
}
    </style>

</head>
<body>

<?php
// No início do carrinho.php, após a abertura do body
if (isset($_SESSION['erro_pedido'])) {
    echo '<div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
            <strong>Erro:</strong> ' . $_SESSION['erro_pedido'] . '
          </div>';
    unset($_SESSION['erro_pedido']);
}
?>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-carrinho">
        <h2>SEU CARRINHO</h2>

        <?php if (empty($produtos)): ?>
            <div class="carrinho-vazio">
                <i class="fas fa-shopping-cart"></i>
                <h3>Seu carrinho está vazio</h3>
                <p>Adicione alguns produtos deliciosos!</p>
                <a href="cardapio.php" class="btn-continuar">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div class="carrinho">
                <div class="itens-carrinho">
                    <form method="POST" action="carrinho.php">
                        <?php foreach ($produtos as $p): ?>
                            <div class="item-carrinho">
                                <div class="item-imagem">
                                    <img src="../assets/img/cardapio/<?php echo htmlspecialchars($p['imagem']); ?>" 
                                         alt="<?php echo htmlspecialchars($p['nome']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                                    <p class="item-descricao"><?php echo htmlspecialchars($p['descricao']); ?></p>
                                    <p class="item-preco">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></p>
                                    
                                    <div class="item-controles">
                                        <div class="quantidade-controle">
                                            <label>Quantidade:</label>
                                            <input type="number" name="quantidade[<?php echo $p['id']; ?>]" 
                                                   value="<?php echo $p['quantidade']; ?>" min="1" max="10">
                                        </div>
                                        
                                        <div class="item-subtotal">
                                            <strong>Subtotal: R$ <?php echo number_format($p['subtotal'], 2, ',', '.'); ?></strong>
                                        </div>
                                        
                                        <a href="remover_carrinho.php?id=<?php echo $p['id']; ?>" 
                                           class="btn-remover" 
                                           onclick="return confirm('Remover este item do carrinho?')">
                                            <i class="fas fa-trash"></i> Remover
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="carrinho-acoes">
                            <button type="submit" name="atualizar_carrinho" class="btn-atualizar">
                                <i class="fas fa-sync"></i> Atualizar Carrinho
                            </button>
                            <a href="cardapio.php" class="btn-continuar">
                                <i class="fas fa-arrow-left"></i> Continuar Comprando
                            </a>
                        </div>
                    </form>
                </div>

                <div class="resumo-pedido">
                    <h3>RESUMO DO PEDIDO</h3>
                    
                    <div class="resumo-item">
                        <span>Subtotal:</span>
                        <strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong>
                    </div>
                    
                    <div class="resumo-item">
                        <span>Taxa de entrega:</span>
                        <strong>R$ <?php echo number_format($taxa_entrega, 2, ',', '.'); ?></strong>
                    </div>

                    <?php if ($cupom_aplicado): ?>
                        <div class="resumo-item cupom-aplicado">
                            <span>Cupom <?php echo htmlspecialchars($cupom_aplicado); ?>:</span>
                            <strong>-<?php echo $desconto; ?>%</strong>
                        </div>
                    <?php endif; ?>

                    <div class="resumo-item total">
                        <span>Total:</span>
                        <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                    </div>

                    <!-- Adicione estas seções após o resumo do pedido no carrinho.php -->

<div class="secao-entrega">
    <h4>Endereço de Entrega</h4>
    
    <?php
    // Buscar endereços do usuário
    require_once "../includes/connect.php";
    $sqlEnderecos = "SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY principal DESC, id DESC";
    $stmtEnderecos = $pdo->prepare($sqlEnderecos);
    $stmtEnderecos->execute([$usuario_id]);
    $enderecos = $stmtEnderecos->fetchAll(PDO::FETCH_ASSOC);
    
    $endereco_principal = null;
    foreach ($enderecos as $endereco) {
        if ($endereco['principal']) {
            $endereco_principal = $endereco;
            break;
        }
    }
    
    // Se não tem principal, usa o primeiro
    if (!$endereco_principal && count($enderecos) > 0) {
        $endereco_principal = $enderecos[0];
    }
    ?>
    
    <?php if (count($enderecos) > 0 && $endereco_principal): ?>
        <!-- Endereço Atual -->
        <div class="endereco-atual" id="enderecoSelecionado">
            <p><strong><?php echo htmlspecialchars($endereco_principal['titulo']); ?></strong></p>
            <p><?php echo htmlspecialchars($endereco_principal['logradouro']); ?>, <?php echo htmlspecialchars($endereco_principal['numero']); ?></p>
            <?php if (!empty($endereco_principal['complemento'])): ?>
                <p>Complemento: <?php echo htmlspecialchars($endereco_principal['complemento']); ?></p>
            <?php endif; ?>
            <p><?php echo htmlspecialchars($endereco_principal['bairro']); ?> - <?php echo htmlspecialchars($endereco_principal['cidade']); ?>/<?php echo htmlspecialchars($endereco_principal['estado']); ?></p>
            <p>CEP: <?php echo htmlspecialchars($endereco_principal['cep']); ?></p>
        </div>
        
        <!-- Seletor de Endereço (se tiver mais de um) -->
        <?php if (count($enderecos) > 1): ?>
            <div class="seletor-endereco">
                <label for="selecionar-endereco"><strong>Escolher outro endereço:</strong></label>
                <select id="selecionar-endereco" class="form-control">
                    <?php foreach ($enderecos as $endereco): ?>
                        <option value="<?php echo $endereco['id']; ?>" 
                                data-titulo="<?php echo htmlspecialchars($endereco['titulo']); ?>"
                                data-logradouro="<?php echo htmlspecialchars($endereco['logradouro']); ?>"
                                data-numero="<?php echo htmlspecialchars($endereco['numero']); ?>"
                                data-complemento="<?php echo htmlspecialchars($endereco['complemento']); ?>"
                                data-bairro="<?php echo htmlspecialchars($endereco['bairro']); ?>"
                                data-cidade="<?php echo htmlspecialchars($endereco['cidade']); ?>"
                                data-estado="<?php echo htmlspecialchars($endereco['estado']); ?>"
                                data-cep="<?php echo htmlspecialchars($endereco['cep']); ?>"
                                <?php echo $endereco['id'] == $endereco_principal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($endereco['titulo']); ?> - 
                            <?php echo htmlspecialchars($endereco['logradouro']); ?>, <?php echo htmlspecialchars($endereco['numero']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <input type="hidden" name="endereco_entrega_id" id="endereco_entrega_id" value="<?php echo $endereco_principal['id']; ?>">
        
    <?php else: ?>
        <!-- Mensagem se não tiver endereços -->
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Nenhum endereço cadastrado.</strong></p>
            <p>Você precisa cadastrar um endereço para finalizar o pedido.</p>
        </div>
    <?php endif; ?>
    
    <div class="endereco-actions">
        <button type="button" class="btn-alterar-endereco" onclick="window.location.href='perfil.php?tab=enderecos'">
            <i class="fas fa-edit"></i> Gerenciar Endereços
        </button>
        
        <?php if (count($enderecos) == 0): ?>
            <button type="button" class="btn-adicionar-endereco" onclick="window.location.href='perfil.php?tab=enderecos'">
                <i class="fas fa-plus"></i> Cadastrar Endereço
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Seção Observações -->
<!-- Na seção de observações, atualize o textarea: -->
<div class="secao-observacoes">
    <h4>OBSERVAÇÕES</h4>
    <textarea 
        class="textarea-observacoes" 
        name="observacoes_text"
        id="observacoes_text"
        placeholder="Alguma observação especial para o seu pedido? (ex: ponto da carne, restrições alimentares, etc.)"
    ></textarea>
</div>

<!-- Seção Informações -->
<div class="secao-informacoes">
    <h4>INFORMAÇÕES</h4>
    <ul class="lista-informacoes">
        <li><strong>Taxa de entrega:</strong> R$ 8,00</li>
        <li><strong>Tempo estimado:</strong> 40-60 minutos</li>
        <li><strong>Formas de pagamento:</strong> Cartão de crédito/débito, dinheiro ou PIX</li>
    </ul>
</div>

<!-- Substitua o formulário de finalizar pedido por este: -->
<form action="finalizar_pedido.php" method="post" id="formFinalizarPedido">
    <!-- Campo oculto para o endereço -->
    <input type="hidden" name="endereco_entrega_id" id="endereco_entrega_id_form" 
           value="<?php echo isset($endereco_principal) ? $endereco_principal['id'] : ''; ?>">
    
    <!-- Campo para observações -->
    <input type="hidden" name="observacoes" id="observacoes_input" value="">
    
    <button type="submit" class="btn-finalizar" id="btnFinalizar">
        <i class="fas fa-check"></i> Finalizar Pedido
    </button>
</form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Atualizar quantidade em tempo real
        document.querySelectorAll('input[name^="quantidade"]').forEach(input => {
            input.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Confirmar antes de remover item
        document.querySelectorAll('.btn-remover').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
                    e.preventDefault();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
    const seletorEndereco = document.getElementById('selecionar-endereco');
    if (seletorEndereco) {
        seletorEndereco.addEventListener('change', function() {
            document.getElementById('endereco_entrega_id').value = this.value;
        });
    }
});

<script>
// Debug: Verificar se o formulário está sendo enviado
document.addEventListener('DOMContentLoaded', function() {
    const formFinalizar = document.getElementById('formFinalizarPedido');
    const btnFinalizar = document.getElementById('btnFinalizar');
    const enderecoIdForm = document.getElementById('endereco_entrega_id_form');
    const observacoesInput = document.getElementById('observacoes_input');
    const textareaObservacoes = document.querySelector('.textarea-observacoes');
    
    // Sincronizar campos de endereço
    const seletorEndereco = document.getElementById('selecionar-endereco');
    const enderecoIdHidden = document.getElementById('endereco_entrega_id');
    
    if (seletorEndereco && enderecoIdForm) {
        seletorEndereco.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            atualizarEnderecoSelecionado(selectedOption);
            enderecoIdForm.value = this.value;
            console.log('Endereço alterado para ID:', this.value);
        });
    }
    
    // Sincronizar inicialmente
    if (enderecoIdHidden && enderecoIdForm) {
        enderecoIdForm.value = enderecoIdHidden.value;
        console.log('Endereço inicial:', enderecoIdHidden.value);
    }
    
    // Sincronizar observações
// No JavaScript, atualize a parte das observações:
const textareaObservacoes = document.getElementById('observacoes_text');
const observacoesInput = document.getElementById('observacoes_input');

if (textareaObservacoes && observacoesInput) {
    // Sincronizar quando o usuário digitar
    textareaObservacoes.addEventListener('input', function() {
        observacoesInput.value = this.value;
        console.log('Observações atualizadas:', this.value);
    });
    
    // Sincronizar inicialmente
    observacoesInput.value = textareaObservacoes.value;
}
    // Validar formulário de finalização
    if (formFinalizar && btnFinalizar) {
        formFinalizar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const enderecoId = enderecoIdForm.value;
            console.log('Tentando finalizar pedido...');
            console.log('Endereço ID:', enderecoId);
            console.log('Observações:', observacoesInput.value);
            
            if (!enderecoId) {
                alert('Erro: Nenhum endereço selecionado. Por favor, selecione um endereço de entrega.');
                return false;
            }
            
            // Mostrar loading
            btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            btnFinalizar.disabled = true;
            
            // Enviar formulário após um pequeno delay para visualização do loading
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    }
});

function atualizarEnderecoSelecionado(option) {
    const enderecoDiv = document.getElementById('enderecoSelecionado');
    const enderecoIdInput = document.getElementById('endereco_entrega_id');
    
    // Atualizar ID do endereço
    enderecoIdInput.value = option.value;
    
    // Atualizar visualização do endereço
    const titulo = option.getAttribute('data-titulo');
    const logradouro = option.getAttribute('data-logradouro');
    const numero = option.getAttribute('data-numero');
    const complemento = option.getAttribute('data-complemento');
    const bairro = option.getAttribute('data-bairro');
    const cidade = option.getAttribute('data-cidade');
    const estado = option.getAttribute('data-estado');
    const cep = option.getAttribute('data-cep');
    
    enderecoDiv.innerHTML = `
        <p><strong>${titulo}</strong></p>
        <p>${logradouro}, ${numero}</p>
        ${complemento ? `<p>Complemento: ${complemento}</p>` : ''}
        <p>${bairro} - ${cidade}/${estado}</p>
        <p>CEP: ${cep}</p>
    `;
    
    mostrarMensagemEndereco('Endereço alterado com sucesso!');
}

function mostrarMensagemEndereco(mensagem) {
    const mensagemExistente = document.querySelector('.mensagem-endereco');
    if (mensagemExistente) {
        mensagemExistente.remove();
    }
    
    const mensagemDiv = document.createElement('div');
    mensagemDiv.className = 'mensagem-endereco';
    mensagemDiv.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${mensagem}</span>
    `;
    
    const secaoEndereco = document.querySelector('.secao-entrega');
    secaoEndereco.parentNode.insertBefore(mensagemDiv, secaoEndereco);
    
    setTimeout(() => {
        mensagemDiv.remove();
    }, 3000);
}

// CSS para a mensagem
const estiloMensagem = `
.mensagem-endereco {
    background: #d4edda;
    color: #155724;
    padding: 15px 20px;
    border-radius: 8px;
    border: 1px solid #c3e6cb;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.3s ease;
}

.mensagem-endereco i {
    color: #28a745;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
`;

// Adicionar estilo ao documento
const styleSheet = document.createElement('style');
styleSheet.textContent = estiloMensagem;
document.head.appendChild(styleSheet);
</script>
    </script>
</body>
</html>