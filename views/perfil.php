<?php
include("../includes/connect.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];

$sqlUser = "SELECT nome, email, foto FROM usuarios WHERE id = ?";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([$usuario_id]);
$usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

$sqlPedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC";
$stmtPedidos = $pdo->prepare($sqlPedidos);
$stmtPedidos->execute([$usuario_id]);
$pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

$sqlReservas = "SELECT id, data_reserva, horario, qtd_pessoas, status, observacoes FROM reservas WHERE usuario_id = ?";
$stmtReservas = $pdo->prepare($sqlReservas);
$stmtReservas->execute([$usuario_id]);
$reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Meu Perfil</title>

<style>
    :root {
    --marrom: #802B01;
    --escuro: #421700;
    --claro: #B85F1B;
    --vermelho: #BD1600;
    --amarelo: #DC7700;

        --border-radius: 10px;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        color: var(--dark-color);
        line-height: 1.6;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 90px auto;
        padding: 20px;
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }

    .sidebar {
        width: 100%;
        max-width: 320px;
        background: #fff;
        border-radius: var(--border-radius);
        padding: 30px 25px;
        text-align: center;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }

    .sidebar:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .user-avatar {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .sidebar img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 4px solid var(--vermelho);
        object-fit: cover;
        transition: var(--transition);
    }

    .user-avatar:hover img {
        border-color: var(--vermelho);
        transform: scale(1.05);
    }

    .user-avatar::after {
        content: 'Alterar';
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: var(--amarelo);
        color: white;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.8em;
        opacity: 0;
        transition: var(--transition);
    }

    .user-avatar:hover::after {
        opacity: 1;
    }

    .sidebar h3 {
        margin: 15px 0 5px 0;
        color: var(--vermelho);
        font-size: 1.4em;
        font-weight: bold;
    }

    .sidebar p {
        color: gray;
        margin-bottom: 25px;
        font-size: 0.95em;
    }

    .menu {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .menu a {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 20px;
        border-radius: var(--border-radius);
        text-decoration: none;
        color: black;
        font-weight: 500;
        transition: var(--transition);
        border: 2px solid transparent;
    }

    .menu a i {
        font-size: 1.1em;
    }

    .menu a:hover {
        color: white;
        background: var(--vermelho);
        transform: translateX(5px);
    }

    .menu a.active {
        background: var(--vermelho);
        color: #fff;
        border-color: var(--primary-color);
    }

    .menu a.logout {
        font-weight: bold;
        color: var(--vermelho);
        margin-top: 10px;
    }

    .menu a.logout:hover {
        background: var(--vermelho);
        color: white;
        transform: translateX(5px);
    }

    .content {
        flex: 1;
        min-width: 300px;
        background: #fff;
        padding: 30px;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }

    .content:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .tab {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }

    .tab.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2 {
        color: var(--dark-color);
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light-color);
        font-size: 1.8em;
    }

    .order {
        border: 1px solid var(--vermelho);
        padding: 20px;
        border-radius: var(--border-radius);
        margin-bottom: 15px;
        transition: var(--transition);
    }

    .order:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .order-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .order-id {
        font-size: 1.2em;
        font-weight: bold;
        color: var(--primary-color);
    }

    .order-date {
        color: var(--gray-color);
        font-size: 0.9em;
    }

    .order-total {
        font-size: 1.1em;
        font-weight: bold;
        color: var(--dark-color);
        margin-top: 10px;
    }

    .status{
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-entregue {
        background: #198754;
        color: white;
    }

    .status-pendente {
        background: #fff3cd;
        color: var(--warning-color);
    }

    .status-cancelado {
        background: #f8d7da;
        color: var(--danger-color);
    }

    .form-group {
        margin-bottom: 20px;
        padding: 20px;
    }

    label {
        display: block;
        margin-bottom: 15px;
        font-weight: 600;
        color: var(--vermelho);
    }

    input, button {
        padding: 12px 15px;
        width: 100%;
        margin-top: 5px;
        border-radius: var(--border-radius);
        border: 2px solid #e9ecef;
        font-size: 1em;
        transition: var(--transition);
    }

    input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.1);
    }

    button {
        background: var(--vermelho);
        color: #fff;
        cursor: pointer;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar {
            max-width: 100%;
        }
        
        .content {
            padding: 20px;
            margin-bottom: 90px;
        }
        
        .sidebar img {
            width: 120px;
            height: 120px;
        }
        
        .menu a {
            padding: 12px 15px;
        }
        .content
    }

    @media (max-width: 480px) {
        .container {
            width: 95%;
            margin: 15px auto;
            padding: 10px;
        }
        
        .sidebar, .content {
            padding: 20px 15px;
        }
        
        h2 {
            font-size: 1.5em;
        }
        
        .order {
            padding: 15px;
        }
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-color);
    }

    .empty-state i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .enderecos-container {
    max-width: 800px;
}

.enderecos-lista {
    display: grid;
    gap: 20px;
    margin-bottom: 30px;
}

.endereco-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.3s ease;
}

.endereco-card.endereco-principal {
    border-color: var(--vermelho);
    background: #f8fff9;
}

.endereco-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.endereco-header h4 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.badge-principal {
    background: var(--vermelho);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.endereco-info p {
    margin: 5px 0;
    color: #666;
    line-height: 1.4;
}

.endereco-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.endereco-actions button {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-definir-principal {
    background: #007bff;
    color: white;
}

.btn-editar-endereco {
    background: var(--amarelo);
    color: white;
}

.btn-excluir-endereco {
    background: var(--vermelho);
    color: white;
}

.btn-adicionar-endereco {
    background: var(--vermelho);
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-adicionar-endereco:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

#formEndereco {
    padding: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 25px;
}

.btn-cancelar, .btn-salvar {
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-cancelar {
    background: #6c757d;
    color: white;
}

.btn-salvar {
    background: var(--vermelho);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .endereco-actions {
        flex-direction: column;
    }
    
    .endereco-actions button {
        width: 100%;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}

.form-dados {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
}

.form-dados input[type="text"],
.form-dados input[type="email"],
.form-dados input[type="password"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    box-sizing: border-box;
}

.form-dados input:focus {
    outline: none;
    border-color: var(--vermelho);
}

.form-dados button[type="submit"] {
    width: 100%;
    padding: 12px;
    background: var(--vermelho);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}


</style>
</head>

<body>

<?php include("../includes/header.php"); ?>

<div class="container">

    <div class="sidebar">

        <form action="../includes/upload_foto.php" method="POST" enctype="multipart/form-data">
            <label style="cursor:pointer;">
                <img src="<?php echo $usuario['foto'] ? '../uploads/'.$usuario['foto'] : 'https://via.placeholder.com/130'; ?>">
                <input type="file" name="foto" style="display:none" onchange="this.form.submit()">
            </label>
        </form>

        <h3><?php echo $usuario['nome']; ?></h3>
        <p><?php echo $usuario['email']; ?></p>

<div class="menu">
    <a class="tab-btn active" data-tab="pedidos">Meus Pedidos</a>
    <a class="tab-btn" data-tab="dados">Meus Dados</a>
    <a class="tab-btn" data-tab="enderecos">Meus Endereços</a>
    <a class="tab-btn" data-tab="reservas">Minhas Reservas</a>
    <a class="logout" href="../includes/logout.php">Logout</a>

</div>
    </div>

    <!-- CONTEÚDO -->
    <div class="content">

        <div id="pedidos" class="tab active">
            <h2>Meus Pedidos</h2>

            <?php if ($pedidos): ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="order">
                        <strong>Pedido #<?php echo $pedido['id']; ?></strong><br>
                        <small><?php echo date("d/m/Y H:i", strtotime($pedido["data_pedido"])); ?></small>
                        <br><br>

                        <span class="status<?php echo strtolower($pedido['status']);?>">
                            <?php echo ucfirst($pedido['status']); ?>
                        </span>

                        <br><br>
                        <strong>Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum pedido encontrado.</p>
            <?php endif; ?>
        </div>

        <div id="dados" class="tab">

            <h2>Meus Dados</h2>

            <form class="form-dados" action="../includes/update_dados.php" method="POST">

                Nome:
                <input type="text" name="nome" value="<?php echo $usuario['nome']; ?>">

                Email:
                <input type="email" name="email" value="<?php echo $usuario['email']; ?>">

                Nova senha (opcional):
                <input type="password" name="senha">

                <button type="submit">Salvar alterações</button>

            </form>

        </div>

        <div id="enderecos" class="tab">
    <h2>Meus Endereços</h2>
    
    <div class="enderecos-container">
        <div class="enderecos-lista" id="listaEnderecos">
            <?php
            $sqlEnderecos = "SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY principal DESC, id DESC";
            $stmtEnderecos = $pdo->prepare($sqlEnderecos);
            $stmtEnderecos->execute([$usuario_id]);
            $enderecos = $stmtEnderecos->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (count($enderecos) > 0): ?>
                <?php foreach ($enderecos as $endereco): ?>
                    <div class="endereco-card <?php echo $endereco['principal'] ? 'endereco-principal' : ''; ?>" 
                         data-endereco-id="<?php echo $endereco['id']; ?>">
                        <div class="endereco-header">
                            <h4><?php echo htmlspecialchars($endereco['titulo']); ?></h4>
                            <?php if ($endereco['principal']): ?>
                                <span class="badge-principal">Principal</span>
                            <?php endif; ?>
                        </div>
                        <div class="endereco-info">
                            <p><?php echo htmlspecialchars($endereco['logradouro']); ?>, <?php echo htmlspecialchars($endereco['numero']); ?></p>
                            <?php if (!empty($endereco['complemento'])): ?>
                                <p>Complemento: <?php echo htmlspecialchars($endereco['complemento']); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($endereco['bairro']); ?> - <?php echo htmlspecialchars($endereco['cidade']); ?>/<?php echo htmlspecialchars($endereco['estado']); ?></p>
                            <p>CEP: <?php echo htmlspecialchars($endereco['cep']); ?></p>
                        </div>
                        <div class="endereco-actions">
                            <?php if (!$endereco['principal']): ?>
                                <button class="btn-definir-principal" 
                                        onclick="definirEnderecoPrincipal(<?php echo $endereco['id']; ?>)">
                                    Tornar Principal
                                </button>
                            <?php endif; ?>
                            <button class="btn-editar-endereco" 
                                    onclick="editarEndereco(<?php echo $endereco['id']; ?>)">
                                Editar
                            </button>
                            <button class="btn-excluir-endereco" 
                                    onclick="excluirEndereco(<?php echo $endereco['id']; ?>)">
                                Excluir
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Nenhum endereço cadastrado</p>
                </div>
            <?php endif; ?>
        </div>
        

        <button class="btn-adicionar-endereco" onclick="abrirModalEndereco()">
            <i class="fas fa-plus"></i> Adicionar Novo Endereço
        </button>
    </div>
</div>

<!-- Modal para adicionar/editar endereço -->
<div id="modalEndereco" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalEnderecoTitulo">Adicionar Endereço</h3>
            <span class="close" onclick="fecharModalEndereco()">&times;</span>
        </div>
        <form id="formEndereco" method="POST" action="../includes/salvar_endereco.php">
            <input type="hidden" name="endereco_id" id="endereco_id" value="">
            
            <div class="form-group">
                <label for="titulo">Título do Endereço*</label>
                <input type="text" id="titulo" name="titulo" required 
                       placeholder="Ex: Casa, Trabalho, etc.">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cep">CEP*</label>
                    <input type="text" id="cep" name="cep" required 
                           placeholder="00000-000" maxlength="9">
                </div>
                <div class="form-group">
                    <label for="numero">Número*</label>
                    <input type="text" id="numero" name="numero" required 
                           placeholder="123">
                </div>
            </div>
            
            <div class="form-group">
                <label for="logradouro">Rua*</label>
                <input type="text" id="logradouro" name="logradouro" required 
                       placeholder="Rua, Avenida, etc.">
            </div>
            
            <div class="form-group">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento" 
                       placeholder="Apartamento, Bloco, etc.">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bairro">Bairro*</label>
                    <input type="text" id="bairro" name="bairro" required>
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade*</label>
                    <input type="text" id="cidade" name="cidade" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado*</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecione</option>
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AP">Amapá</option>
                        <option value="AM">Amazonas</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SP">São Paulo</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="principal" name="principal" value="1">
                <label for="principal">Definir como endereço principal</label>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancelar" onclick="fecharModalEndereco()">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar Endereço</button>
            </div>
        </form>
    </div>
</div>

 <div id="reservas" class="tab">
            <h2>Minhas Reservas</h2>

            <?php if ($reservas): ?>
                <?php foreach ($reservas as $reserva): ?>
                    <div class="order">
                        <strong>Reserva #<?php echo $reserva['id']; ?></strong><br>
                        <strong><?php echo date('d/m/Y', strtotime($reserva['data_reserva'])); ?></strong>
                        <strong><?php echo date('H:i', strtotime($reserva['horario'])); ?></strong>
                        <br><br>

                        <span class="qtd<?php echo strtolower($reserva ['qtd_pessoas']); ?>">
                            Reserva para <?php echo ucfirst($reserva['qtd_pessoas']); ?> Pessoas
                        </span>

                        <br><br>

                        <span class="status<?php echo strtolower($reserva['status']); ?>">
                            <?php echo ucfirst($reserva['status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum pedido encontrado.</p>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {

        document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
        document.getElementById(btn.dataset.tab).classList.add("active");

    });
});

function abrirModalEndereco() {
    document.getElementById('modalEndereco').style.display = 'block';
    document.getElementById('modalEnderecoTitulo').textContent = 'Adicionar Endereço';
    document.getElementById('formEndereco').reset();
    document.getElementById('endereco_id').value = '';
}

function fecharModalEndereco() {
    document.getElementById('modalEndereco').style.display = 'none';
}

function editarEndereco(enderecoId) {
    fetch(`../includes/buscar_endereco.php?id=${enderecoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalEnderecoTitulo').textContent = 'Editar Endereço';
                document.getElementById('endereco_id').value = data.endereco.id;
                document.getElementById('titulo').value = data.endereco.titulo;
                document.getElementById('cep').value = data.endereco.cep;
                document.getElementById('logradouro').value = data.endereco.logradouro;
                document.getElementById('numero').value = data.endereco.numero;
                document.getElementById('complemento').value = data.endereco.complemento || '';
                document.getElementById('bairro').value = data.endereco.bairro;
                document.getElementById('cidade').value = data.endereco.cidade;
                document.getElementById('estado').value = data.endereco.estado;
                document.getElementById('principal').checked = data.endereco.principal == 1;
                
                document.getElementById('modalEndereco').style.display = 'block';
            } else {
                alert('Erro ao carregar endereço');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao carregar endereço');
        });
}

function definirEnderecoPrincipal(enderecoId) {
    if (confirm('Deseja definir este endereço como principal?')) {
        fetch('../includes/definir_endereco_principal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `endereco_id=${enderecoId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao definir endereço principal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao definir endereço principal');
        });
    }
}

function excluirEndereco(enderecoId) {
    if (confirm('Tem certeza que deseja excluir este endereço?')) {
        fetch('../includes/excluir_endereco.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `endereco_id=${enderecoId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao excluir endereço');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao excluir endereço');
        });
    }
}

document.getElementById('cep').addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g, '');
    
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('logradouro').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('estado').value = data.uf;
                    document.getElementById('numero').focus();
                }
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
            });
    }
});

document.getElementById('cep').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    this.value = value;
});
</script>

</body>
</html>
