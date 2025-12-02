<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in usuarios.php. Type: " . gettype($_SESSION));
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_usuario'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];

    $sqlInsert = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    
    if ($stmtInsert->execute([$nome, $email, $senha, $tipo])) {
        $mensagem = "UsuÃ¡rio cadastrado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao cadastrar usuÃ¡rio";
        $tipoMensagem = "error";
    }
}


if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    
    
    if ($id == $_SESSION['id']) {
        $mensagem = "NÃ£o Ã© possÃ­vel excluir seu prÃ³prio usuÃ¡rio.";
        $tipoMensagem = "error";
    } else {
        $sqlDelete = "DELETE FROM usuarios WHERE id = ?";
        $stmtDelete = $pdo->prepare($sqlDelete);
        
        if ($stmtDelete->execute([$id])) {
            $mensagem = "UsuÃ¡rio excluÃ­do com sucesso!";
            $tipoMensagem = "success";
        } else {
            $mensagem = "Erro ao excluir usuÃ¡rio";
            $tipoMensagem = "error";
        }
    }
}


$sqlUsuarios = "SELECT * FROM usuarios ORDER BY nome";
$stmtUsuarios = $pdo->query($sqlUsuarios);
$usuarios = $stmtUsuarios->fetchAll();


$sqlCounters = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN tipo = 'cliente' THEN 1 ELSE 0 END) as clientes
    FROM usuarios";
$stmtCounters = $pdo->query($sqlCounters);
$counters = $stmtCounters->fetch();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestÃ£o de UsuÃ¡rios - Admin</title>
    <link rel="stylesheet" href="https:
    <link href="https:
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        
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

        
        .app-btn-sm { padding: 6px 12px; font-size: 0.875rem; }
        .app-btn-lg { padding: 12px 20px; font-size: 1.1rem; }

        
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

        
        .table {
            margin-bottom: 0;
        }

        .table td {
            vertical-align: middle;
        }

        
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
                <span>CardÃ¡pio</span>
            </a>
            <a href="clientes.php" class="menu-item active">
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
                <input type="text" placeholder="Buscar usuÃ¡rios..." id="searchInput">
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>GestÃ£o de UsuÃ¡rios</h1>
                <p>Gerencie os usuÃ¡rios do sistema</p>
            </div>

            
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h3><?php echo $counters['total']; ?></h3>
                                    <p class="text-muted">Total de UsuÃ¡rios</p>
                                </div>
                                <div class="col-md-4">
                                    <h3><?php echo $counters['admins']; ?></h3>
                                    <p class="text-muted">Administradores</p>
                                </div>
                                <div class="col-md-4">
                                    <h3><?php echo $counters['clientes']; ?></h3>
                                    <p class="text-muted">Clientes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            
                            <button class="app-btn app-btn-primary app-btn-lg" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                <i class="fas fa-plus"></i>
                                <span>Novo UsuÃ¡rio</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>AÃ§Ãµes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $usuario['tipo'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                <?php echo ucfirst($usuario['tipo']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($usuario['id'] != $_SESSION['id']): ?>
                                                
                                                <button class="app-btn app-btn-danger app-btn-sm" onclick="confirmarExclusao(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nome']); ?>')">
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

    
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUsuarioLabel">Cadastrar Novo UsuÃ¡rio</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de UsuÃ¡rio</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="cliente">Cliente</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        
                        <button type="button" class="app-btn app-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="app-btn app-btn-primary" name="cadastrar_usuario">
                            <i class="fas fa-save"></i> Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Confirmar exclusÃ£o</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuÃ¡rio <strong id="deleteUsuarioNome"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger" data-id="">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https:
    <script>
        
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        
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

        
        function confirmarExclusao(id, nome) {
            document.getElementById('deleteUsuarioNome').textContent = nome;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.dataset.id = id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = `clientes.php?excluir=${id}`;
        });

        
        document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });

        
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