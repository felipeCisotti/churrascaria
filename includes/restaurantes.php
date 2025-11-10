<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in restaurantes_dash.php. Type: " . gettype($_SESSION));
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

include("connect.php");

$userName = isset($_SESSION["nome"]) ? htmlspecialchars($_SESSION["nome"]) : "Admin";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $estado = $_POST['estado'];
    $cidade = $_POST['cidade'];

    $sql = "INSERT INTO restaurantes (nome, estado, cidade) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $estado, $cidade]);

    // Redireciona para limpar o POST e evitar duplicação
    header("Location: restaurantes.php?sucesso=1");
    exit;
}


// Processar exclusão de usuário

if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];

    $sqlDelete = "DELETE FROM restaurantes WHERE id = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);

    if ($stmtDelete->execute([$id])) {
        $mensagem = "Restaurante excluído com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao excluir Restaurante!";
        $tipoMensagem = "error";
    }

    // Evita que a página seja recarregada com o GET novamente
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Buscar todos os usuários
$sqlRestaurantes = "SELECT * FROM restaurantes ORDER BY nome";
$stmtRestaurantes = $pdo->query($sqlRestaurantes);
$restaurantes = $stmtRestaurantes->fetchAll();

// Contadores
$sqlCounters = "SELECT COUNT(*) FROM restaurantes";
$stmtCounters = $pdo->query($sqlCounters);
$counters = $stmtCounters->fetchColumn(); // retorna o número diretamente

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Restaurantes - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Botões personalizados sem hover */
        .app-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            line-height: 1;
            border: 1px solid transparent;
            cursor: pointer;
            transition: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .app-btn i {
            font-size: 1rem;
        }

        .app-btn-primary {
            background: #2563eb;
            color: white;
        }

        .app-btn-danger {
            background: #dc3545;
            color: white;
        }

        .app-btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* Tamanhos */
        .app-btn-sm { padding: 6px 12px; font-size: 0.875rem; }
        .app-btn-lg { padding: 12px 20px; font-size: 1.1rem; }

        /* Forçar visibilidade dos botões */
        .btn, 
        .app-btn,
        .card-footer .btn,
        .btn-group .btn {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: none !important;
            transition: none !important;
        }

        /* Ajustes da tabela */
        .table {
            margin-bottom: 0;
        }

        .table td {
            vertical-align: middle;
        }

        /* Badge personalizado */
        .badge-role {
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-admin {
            background: #dc3545;
            color: white;
        }

        .badge-cliente {
            background: #0d6efd;
            color: white;
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
            <a href="cardapio_dash.php" class="menu-item">
                <i class="fa-solid fa-utensils"></i>
                <span>Cardápio</span>
            </a>
            <a href="clientes.php" class="menu-item ">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="restaurantes.php" class="menu-item active">
                <i class="fa-solid fa-location-dot"></i>
                <span>Restaurantes</span>
            </a>
            <a href="financeiro.php" class="menu-item">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>Financeiro</span>
            </a>
            <a href=" relatorios.php" class="menu-item">
                <i class="fa-solid fa-chart-line"></i>
                <span>Relatórios</span>
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
                <input type="text" placeholder="Buscar restaurantes..." id="searchInput">
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Gestão de Restaurantes</h1>
                <p>Gerencie os restaurantes.</p>
            </div>

            <!-- Mensagens -->
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-7">
                                    <h3><?php echo $counters; ?></h3>
                                    <p class="text-muted">Total de Restaurantes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <!-- Botão Novo Usuário -->
                            <button class="app-btn app-btn-primary app-btn-lg" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                <i class="fas fa-plus"></i>
                                <span>Novo Restaurante</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Usuários -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Estado</th>
                                    <th>Cidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php foreach ($restaurantes as $restaurante): ?>
        <tr>
            <td><?php echo htmlspecialchars($restaurante['nome']); ?></td>
            <td><?php echo htmlspecialchars($restaurante['estado']); ?></td>
            <td><?php echo htmlspecialchars($restaurante['cidade']); ?></td>
            <td>
                <?php if ($restaurante['id'] != $_SESSION['id']): ?>
                    <button class="app-btn app-btn-danger app-btn-sm"
                        onclick="confirmarExclusao(<?php echo $restaurante['id']; ?>, '<?php echo htmlspecialchars($restaurante['nome']); ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Cadastro de Usuário -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUsuarioLabel">Cadastrar Novo Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado" required>
                        </div>
                        <div class="mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- No modal -->
                        <button type="button" class="app-btn app-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="app-btn app-btn-primary" name="cadastrar_restaurantes">
                            <i class="fas fa-save"></i> Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Confirmar exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="deleteUsuarioNome"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger" data-id="">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Busca aprimorada
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const nome = row.querySelector('td:first-child').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const tipo = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                const matches = nome.includes(searchTerm) || 
                               email.includes(searchTerm) || 
                               tipo.includes(searchTerm);
                
                row.style.display = matches ? '' : 'none';
            });
        });

        // Confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('deleteUsuarioNome').textContent = nome;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.dataset.id = id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = `restaurantes.php?excluir=${id}`;
        });

        // Limpar campos do modal ao fechar
        document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });

        // Validação do formulário
        document.querySelector('#modalUsuario form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>