<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in produtos.php. Type: " . gettype($_SESSION));
    header("location: views/login.html");
    exit;
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: views/login.html");
    exit;
}

if (!isset($_SESSION["tipo"]) || $_SESSION["tipo"] !== "admin") {
    header("location: index.php");
    exit;
}

include ("connect.php");

$userName = isset($_SESSION["nome"]) ? htmlspecialchars($_SESSION["nome"]) : "Admin";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {

        $uploadDir = '../assets/img/cardapio/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $extensao;

        $caminhoImagem = $uploadDir . $nomeImagem;

        move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem);

        $imagem = $nomeImagem;
    }

    $sqlInsert = "INSERT INTO produtos (nome, descricao, preco, categoria, imagem, ativo) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);

    if ($stmtInsert->execute([$nome, $descricao, $preco, $categoria, $imagem, $ativo])) {
        $mensagem = "Produto cadastrado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao cadastrar produto";
        $tipoMensagem = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_produto'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {

        $uploadDir = '../assets/img/cardapio/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $extensao;

        $caminhoImagem = $uploadDir . $nomeImagem;

        move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem);

        $imagem = $nomeImagem;

    } else {
        $imagem = $_POST['imagem_atual'];
    }

    $sqlUpdate = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, categoria = ?, imagem = ?, ativo = ? WHERE id = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);

    if ($stmtUpdate->execute([$nome, $descricao, $preco, $categoria, $imagem, $ativo, $id])) {
        $mensagem = "Produto atualizado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao atualizar produto";
        $tipoMensagem = "error";
    }
}

if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    try {
        // Verifica se está em pedidos
        $sqlCheckPedidos = "SELECT COUNT(*) FROM itens_pedido WHERE produto_id = ?";
        $stmtCheck = $pdo->prepare($sqlCheckPedidos);
        $stmtCheck->execute([$id]);
        $usoEmPedidos = (int) $stmtCheck->fetchColumn();

        if ($usoEmPedidos > 0) {
            $mensagem = "Não é possível excluir este produto pois está vinculado a pedidos.";
            $tipoMensagem = "error";

        } else {

            $sqlDelete = "DELETE FROM produtos WHERE id = ?";
            $stmtDelete = $pdo->prepare($sqlDelete);

            if ($stmtDelete->execute([$id])) {
                $mensagem = "Produto excluído com sucesso!";
                $tipoMensagem = "success";
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
                exit;

            } else {
                $mensagem = "Erro ao excluir produto";
                $tipoMensagem = "error";
            }
        }

    } catch (Exception $e) {
        $mensagem = "Erro ao processar exclusão.";
        $tipoMensagem = "error";
    }
}

$sqlProdutos = "SELECT * FROM produtos ORDER BY categoria, nome";
$stmtProdutos = $pdo->query($sqlProdutos);
$produtos = $stmtProdutos->fetchAll();

$sqlCategorias = "SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL ORDER BY categoria";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll();

$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todas';

if ($filtro_categoria !== 'todas') {
    $sqlProdutos = "SELECT * FROM produtos WHERE categoria = ? ORDER BY nome";
    $stmtProdutos = $pdo->prepare($sqlProdutos);
    $stmtProdutos->execute([$filtro_categoria]);
    $produtos = $stmtProdutos->fetchAll();
}

$sqlCounters = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
    SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos
    FROM produtos";

$stmtCounters = $pdo->query($sqlCounters);
$counters = $stmtCounters->fetch();

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Estilos adicionais rápidos para melhorar aparência -->
    <style>
        /* === Botões globais personalizados === */
        .app-btn, button, .btn, .btn-acao {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1;
            border: 1px solid transparent;
            cursor: pointer;
            transition: none !important; /* sem hover/transform */
            box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
            background-color: #f6f8fb;
            color: #0f172a;
        }

        /* Variantes */
        .app-btn-primary, .btn-primary {
            background-color: #b90000ff;
            color: #fff;
            border-color: transparent;
        }
        .app-btn-secondary {
            background: linear-gradient(90deg,#e2e8f0,#cbd5e1);
            color: #0f172a;
            border-color: rgba(15,23,42,0.06);
        }
        .app-btn-danger, .btn-outline-danger {
            background: transparent;
            color: #dc3545;
            border-color: rgba(220,53,69,0.12);
        }
        .app-btn-outline {
            background: transparent;
            color: #2563eb;
            border: 1px solid rgba(37,99,235,0.12);
        }

        /* Botão ícone (menu) */
        .app-icon-btn, .menu-toggle {
            width: 44px;
            height: 44px;
            padding: 0;
            justify-content: center;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(2,6,23,0.06);
            color: #111827;
        }

        /* Pequenas variações de tamanho */
        .btn-sm, .app-btn-sm { padding: 6px 10px; font-size: .85rem; border-radius: 8px; }
        .btn-lg, .app-btn-lg { padding: 12px 18px; font-size: 1rem; border-radius: 12px; }

        /* Forçar visibilidade/funcionalidade em card footer */
        .card-footer .btn, .card-footer .btn-acao {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Ajustes responsivos */
        @media (max-width: 576px) {
            .app-btn { width: 100%; justify-content: center; }
            .btn-group.w-100 .btn { width: 49%; }
        }

        /* Cards mais elegantes */
        .produto-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            transition: transform .18s ease, box-shadow .18s ease;
            overflow: hidden;
        }
        .produto-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .produto-imagem { height:200px; object-fit:cover; }

        /* Badges e categoria */
        .categoria-badge {
            background-color: #c50000ff;
            color:#fff;
            padding:6px 10px;
            border-radius: 999px;
            font-size:.8rem;
        }

        /* Status */
        .status-ativo { color: #198754; font-weight:600; }
        .status-inativo { color: #dc3545; font-weight:600; }

        /* Botões de ação */
        .btn-acao { width:48%; }
        .btn-acao i { margin-right:6px; }

        /* Filtro badges */
        .filter-badge {
            cursor: pointer;
            padding:8px 12px;
            border-radius: 999px;
            transition: transform .12s ease;
        }

        /* Ajustes mobile */
        @media (max-width: 576px) {
            .produto-imagem { height:160px; }
            .produto-card { margin-bottom: 1rem; }
        }

        /* Preview imagem dentro do modal */
        #previewImagem img { max-height: 180px; border-radius: 8px; }

        /* === Forçar visibilidade dos botões (corrige quando aparecem só no hover) === */
        /* Torna botões sempre visíveis e clicáveis */
        .card-footer .btn,
        .card-footer .btn-acao,
        .btn,
        .btn-outline-danger,
        .btn-outline-primary {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: none !important;
            box-shadow: none !important;
            transition: none !important;
        }

        /* Neutraliza hover em badges e links usados como botões */
        .filter-badge:hover, .menu-item:hover, a.menu-item:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    </style>
</head>

<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/3.png" alt="Logo">
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Main</div>
            <a href="../dashboard.php" class="menu-item ">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="pedidos_dash.php" class="menu-item">
                <i class="fa-solid fa-clipboard"></i>
                <span>Pedidos</span>
            </a>
            <a href="cardapio_dash.php" class="menu-item active">
                <i class="fa-solid fa-utensils"></i>
                <span>Cardápio</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="restaurantes.php" class="menu-item">
                <i class="fa-solid fa-location-dot"></i>
                <span>Restaurantes</span>
            </a>
            <a href="reservas.php" class="menu-item">
                <i class="fa-solid fa-pen"></i>
                <span>Reservas</span>
            </a>
            <div class="menu-label">Logout</div>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="search-bar" id="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar produtos..." id="searchInput">
            </div>

            <div class="button-store">
                <button>
                    <a href="dashboard.php">VOLTAR AO DASHBOARD</a>
                </button>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Gestão de Cardápio</h1>
                <p>Gerencie os produtos do seu restaurante</p>
            </div>

            <!-- Mensagens -->
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Estatísticas e Botão Cadastrar -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h3><?php echo $counters['total']; ?></h3>
                                    <p class="text-muted">Total de Produtos</p>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-success"><?php echo $counters['ativos']; ?></h3>
                                    <p class="text-muted">Produtos Ativos</p>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-danger"><?php echo $counters['inativos']; ?></h3>
                                    <p class="text-muted">Produtos Inativos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <button class="app-btn app-btn-primary app-btn-lg" data-bs-toggle="modal" data-bs-target="#modalProduto">
                                <i class="fas fa-plus"></i>
                                <span>Novo Usuário</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filtrar por Categoria</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge filter-badge <?php echo $filtro_categoria === 'todas' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href=window.location.pathname.split('/').pop()">
                            Todas as Categorias
                        </span>
                        <?php foreach ($categorias as $categoria): ?>
                            <span class="badge filter-badge <?php echo $filtro_categoria === $categoria['categoria'] ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                                  onclick="window.location.href=window.location.pathname.split('/').pop() + '?categoria=<?php echo urlencode($categoria['categoria']); ?>'">
                                <?php echo htmlspecialchars($categoria['categoria']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Lista de Produtos -->
            <div class="row" id="produtosGrid">
                <?php if (count($produtos) > 0): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <div class="col-md-3 mb-4 produto-item"> <!-- Diminuindo a largura do card -->
                            <div class="card produto-card h-100" style="height: 250px;"> <!-- Diminuindo a altura do card -->
                                <?php if ($produto['imagem']): ?>
                                    <img src="../assets/img/cardapio/<?php echo htmlspecialchars($produto['imagem']); ?>" 
     alt="<?php echo htmlspecialchars($produto['nome']); ?>" style="height: 150px; object-fit: cover; border-radius: 10px;"> <!-- Diminuindo a altura da imagem -->
                                <?php else: ?>
                                    <div class="card-img-top produto-imagem bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="fas fa-utensils fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                        <span class="categoria-badge"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                                    </div>
                                    
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars($produto['descricao']); ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="preco-destaque">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                        <span class="<?php echo $produto['ativo'] ? 'status-ativo' : 'status-inativo'; ?>">
                                            <i class="fas fa-circle"></i> 
                                            <?php echo $produto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="btn w-100">
                                        <button class="btn btn-outline-danger btn-sm btn-delete" 
                                                onclick="confirmarExclusao(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['nome']); ?>')"
                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-utensils fa-4x text-muted mb-3"></i>
                            <h4>Nenhum produto encontrado</h4>
                            <p class="text-muted">
                                <?php echo $filtro_categoria !== 'todas' ? 
                                    'Não há produtos na categoria "' . htmlspecialchars($filtro_categoria) . '"' : 
                                    'Não há produtos cadastrados no sistema'; ?>
                            </p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                                <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmDeleteLabel">Confirmar exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o produto <strong id="deleteProdutoNome"></strong>?</p>
                    <p class="text-muted small">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger btn-sm" data-id="">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cadastro/Edição de Produto (substitua o modal atual por este) -->
    <div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="formProduto" action="cardapio_dash.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProdutoLabel">Cadastrar Novo Produto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="produtoId" value="">
                        <input type="hidden" name="imagem_atual" id="imagemAtual" value="">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nomeProd" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nomeProd" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="precoProd" class="form-label">Preço (R$)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="precoProd" name="preco" required>
                            </div>
                            <div class="col-12">
                                <label for="descricaoProd" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricaoProd" name="descricao" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="categoriaProd" class="form-label">Categoria</label>
                                <input type="text" class="form-control" id="categoriaProd" name="categoria">
                            </div>
                            <div class="col-md-3 d-flex align-items-center">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="ativoProd" name="ativo">
                                    <label class="form-check-label" for="ativoProd">
                                        Ativo
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="imagem" class="form-label">Imagem (substituir)</label>
                                <input class="form-control" type="file" id="imagem" name="imagem" accept="image/*">
                                <div id="previewImagem" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="app-btn app-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="app-btn app-btn-primary" id="btnSubmit" name="cadastrar_produto">Cadastrar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts adicionais para editar / preview -->
    <script>
        async function editarProduto(id) {
            try {
                const resp = await fetch(`get_produto.php?id=${id}`);
                if (!resp.ok) throw new Error('Falha na requisição');
                const produto = await resp.json();
                if (produto.error) {
                    alert(produto.error);
                    return;
                }

                // Preencher campos do modal (usar IDs do modal)
                document.getElementById('produtoId').value = produto.id ?? '';
                document.getElementById('nomeProd').value = produto.nome ?? '';
                document.getElementById('descricaoProd').value = produto.descricao ?? '';
                document.getElementById('precoProd').value = produto.preco ?? '';
                document.getElementById('categoriaProd').value = produto.categoria ?? '';
                document.getElementById('ativoProd').checked = parseInt(produto.ativo) === 1;
                document.getElementById('imagemAtual').value = produto.imagem ?? '';

                // Preview da imagem atual
                const preview = document.getElementById('previewImagem');
                if (produto.imagem) {
                    preview.innerHTML = `<div class="d-flex flex-column">
                        <img src="${produto.imagem}" alt="Imagem atual" class="img-fluid rounded" style="max-height:180px;">
                        <small class="text-muted mt-1">Imagem atual (será substituída se enviar novo arquivo)</small>
                    </div>`;
                } else {
                    preview.innerHTML = '<small class="text-muted">Sem imagem</small>';
                }

                // Ajustar modal para edição
                document.getElementById('modalProdutoLabel').textContent = 'Editar Produto';
                const btn = document.getElementById('btnSubmit');
                btn.name = 'editar_produto';
                btn.innerHTML = '<i class="fas fa-save"></i> Atualizar Produto';

                // Abrir modal
                const modalEl = document.getElementById('modalProduto');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } catch (err) {
                console.error(err);
                alert('Erro ao carregar dados do produto.');
            }
        }

        // Preview ao selecionar arquivo (apenas um listener)
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'imagem') {
                const file = e.target.files[0];
                const preview = document.getElementById('previewImagem');
                if (!file) {
                    const atual = document.getElementById('imagemAtual').value;
                    preview.innerHTML = atual ? `<img src="${atual}" alt="Imagem atual" class="img-fluid rounded" style="max-height:180px;">` : '<small class="text-muted">Sem imagem</small>';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" class="img-fluid rounded" style="max-height:180px;">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Confirmar exclusão (mantém comportamento anterior)
        function confirmarExclusao(id, nome) {
            document.getElementById('deleteProdutoNome').textContent = nome;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.dataset.id = id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }
        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            const id = this.dataset.id;
            const base = window.location.pathname.split('/').pop();
            window.location.href = `${base}?excluir=${id}`;
        });

        // Resetar modal ao fechar
        document.getElementById('modalProduto').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('formProduto');
            form.reset();
            document.getElementById('produtoId').value = '';
            document.getElementById('imagemAtual').value = '';
            document.getElementById('previewImagem').innerHTML = '';
            document.getElementById('modalProdutoLabel').textContent = 'Cadastrar Novo Produto';
            const btn = document.getElementById('btnSubmit');
            btn.name = 'cadastrar_produto';
            btn.innerHTML = 'Cadastrar Produto';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu toggle
        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Busca em tempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const produtos = document.querySelectorAll('.produto-item');
            
            produtos.forEach(produto => {
                const text = produto.textContent.toLowerCase();
                produto.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Preview da imagem
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('previewImagem');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Função para editar produto
        function editarProduto(id) {
            fetch(`get_produto.php?id=${id}`)
                .then(response => response.json())
                .then(produto => {
                    document.getElementById('produtoId').value = produto.id;
                    document.getElementById('nome').value = produto.nome;
                    document.getElementById('descricao').value = produto.descricao;
                    document.getElementById('preco').value = produto.preco;
                    document.getElementById('categoria').value = produto.categoria;
                    document.getElementById('ativo').checked = produto.ativo == 1;
                    document.getElementById('imagemAtual').value = produto.imagem;
                    
                    // Atualizar preview da imagem
                    const preview = document.getElementById('previewImagem');
                    if (produto.imagem) {
                        preview.innerHTML = `<img src="${produto.imagem}" class="img-thumbnail" style="max-height: 200px;">
                                            <div class="form-text">Imagem atual</div>`;
                    } else {
                        preview.innerHTML = '';
                    }
                    
                    document.getElementById('modalProdutoLabel').textContent = 'Editar Produto';
                    document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-save"></i> Atualizar Produto';
                    document.getElementById('btnSubmit').name = 'editar_produto';
                    
                    new bootstrap.Modal(document.getElementById('modalProduto')).show();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do produto');
                });
        }

        function confirmarExclusao(id, nome) {
            document.getElementById('deleteProdutoNome').textContent = nome;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.dataset.id = id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        // Ação do botão de confirmação no modal
        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            const id = this.dataset.id;
            const base = window.location.pathname.split('/').pop();
            window.location.href = `${base}?excluir=${id}`;
        });

        // Resetar modal ao fechar
        document.getElementById('modalProduto').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formProduto').reset();
            document.getElementById('previewImagem').innerHTML = '';
            document.getElementById('modalProdutoLabel').textContent = 'Cadastrar Novo Produto';
            document.getElementById('btnSubmit').innerHTML = 'Cadastrar Produto';
            document.getElementById('btnSubmit').name = 'cadastrar_produto';
            document.getElementById('produtoId').value = '';
            document.getElementById('imagemAtual').value = '';
        });

        // Fechar sidebar ao clicar fora (mobile)
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');

            if (window.innerWidth <= 576 &&
                !sidebar.contains(event.target) &&
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html>