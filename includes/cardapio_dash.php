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

// Processar cadastro de novo produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_produto'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Processar upload de imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $uploadDir = 'uploads/produtos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $extensao;
        $caminhoImagem = $uploadDir . $nomeImagem;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
            $imagem = $caminhoImagem;
        }
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

// Processar edição de produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_produto'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Se há nova imagem, processar upload
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $uploadDir = 'uploads/produtos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $extensao;
        $caminhoImagem = $uploadDir . $nomeImagem;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
            // Deletar imagem antiga se existir
            $sqlImagemAntiga = "SELECT imagem FROM produtos WHERE id = ?";
            $stmtImagem = $pdo->prepare($sqlImagemAntiga);
            $stmtImagem->execute([$id]);
            $imagemAntiga = $stmtImagem->fetchColumn();
            
            if ($imagemAntiga && file_exists($imagemAntiga)) {
                unlink($imagemAntiga);
            }
            
            $imagem = $caminhoImagem;
        }
    } else {
        // Manter imagem existente
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

// Processar exclusão de produto
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    
    // Verificar se o produto está em algum pedido antes de excluir
    $sqlCheckPedidos = "SELECT COUNT(*) FROM itens_pedido WHERE produto_id = ?";
    $stmtCheck = $pdo->prepare($sqlCheckPedidos);
    $stmtCheck->execute([$id]);
    $usoEmPedidos = $stmtCheck->fetchColumn();
    
    if ($usoEmPedidos > 0) {
        $mensagem = "Não é possível excluir este produto pois está vinculado a pedidos.";
        $tipoMensagem = "error";
    } else {
        // Deletar imagem se existir
        $sqlImagem = "SELECT imagem FROM produtos WHERE id = ?";
        $stmtImagem = $pdo->prepare($sqlImagem);
        $stmtImagem->execute([$id]);
        $imagem = $stmtImagem->fetchColumn();
        
        if ($imagem && file_exists($imagem)) {
            unlink($imagem);
        }
        
        $sqlDelete = "DELETE FROM produtos WHERE id = ?";
        $stmtDelete = $pdo->prepare($sqlDelete);
        
        if ($stmtDelete->execute([$id])) {
            $mensagem = "Produto excluído com sucesso!";
            $tipoMensagem = "success";
        } else {
            $mensagem = "Erro ao excluir produto";
            $tipoMensagem = "error";
        }
    }
}

// Buscar todos os produtos
$sqlProdutos = "SELECT * FROM produtos ORDER BY categoria, nome";
$stmtProdutos = $pdo->query($sqlProdutos);
$produtos = $stmtProdutos->fetchAll();

// Buscar categorias únicas para o filtro
$sqlCategorias = "SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL ORDER BY categoria";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll();

// Filtro por categoria
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todas';
if ($filtro_categoria !== 'todas') {
    $sqlProdutos = "SELECT * FROM produtos WHERE categoria = ? ORDER BY nome";
    $stmtProdutos = $pdo->prepare($sqlProdutos);
    $stmtProdutos->execute([$filtro_categoria]);
    $produtos = $stmtProdutos->fetchAll();
}

// Contadores
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
</head>

<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="https://ui-avatars.com/api/?name=Admin+Chefe&background=3498db&color=fff" alt="Logo">
            <h2>Bem vindo <?php echo $userName; ?></h2>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Main</div>
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="pedidos.php" class="menu-item">
                <i class="fa-solid fa-clipboard"></i>
                <span>Pedidos</span>
            </a>
            <a href="produtos.php" class="menu-item active">
                <i class="fa-solid fa-utensils"></i>
                <span>Cardápio</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="financeiro.php" class="menu-item">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>Financeiro</span>
            </a>
            <a href="avaliacoes.php" class="menu-item">
                <i class="fa-solid fa-star"></i>
                <span>Avaliações</span>
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
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalProduto">
                                <i class="fas fa-plus"></i> NOVO PRODUTO
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
                              onclick="window.location.href='produtos.php'">
                            Todas as Categorias
                        </span>
                        <?php foreach ($categorias as $categoria): ?>
                            <span class="badge filter-badge <?php echo $filtro_categoria === $categoria['categoria'] ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                                  onclick="window.location.href='produtos.php?categoria=<?php echo urlencode($categoria['categoria']); ?>'">
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
                        <div class="col-md-4 mb-4 produto-item">
                            <div class="card produto-card h-100">
                                <?php if ($produto['imagem']): ?>
                                    <img src="<?php echo $produto['imagem']; ?>" class="card-img-top produto-imagem" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top produto-imagem bg-light d-flex align-items-center justify-content-center">
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
                                    <div class="btn-group w-100">
                                        <button class="btn btn-outline-primary btn-sm btn-acao" 
                                                onclick="editarProduto(<?php echo $produto['id']; ?>)"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm btn-acao" 
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

    <!-- Modal para Cadastro/Edição de Produto -->
    <div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoLabel">Cadastrar Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="formProduto">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="produtoId">
                        <input type="hidden" name="imagem_atual" id="imagemAtual">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome do Produto *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="preco" class="form-label">Preço *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">Categoria</label>
                                    <input type="text" class="form-control" id="categoria" name="categoria" 
                                           placeholder="Ex: Carnes, Acompanhamentos, Bebidas...">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                              placeholder="Descreva o produto..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="imagem" class="form-label">Imagem do Produto</label>
                                    <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                                    <div class="form-text">Formatos: JPG, PNG, GIF. Tamanho máximo: 2MB</div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" checked>
                                    <label class="form-check-label" for="ativo">Produto ativo no cardápio</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div id="previewImagem" class="text-center mt-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" name="cadastrar_produto" id="btnSubmit">
                            Cadastrar Produto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    
                    // Atualizar modal
                    document.getElementById('modalProdutoLabel').textContent = 'Editar Produto';
                    document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-save"></i> Atualizar Produto';
                    document.getElementById('btnSubmit').name = 'editar_produto';
                    
                    // Abrir modal
                    new bootstrap.Modal(document.getElementById('modalProduto')).show();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados do produto');
                });
        }

        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o produto "${nome}"?`)) {
                window.location.href = `produtos.php?excluir=${id}`;
            }
        }

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