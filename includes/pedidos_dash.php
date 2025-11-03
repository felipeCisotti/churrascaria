<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in pedidos.php. Type: " . gettype($_SESSION));
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

// Processar atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id']) && isset($_POST['novo_status'])) {
    $pedido_id = $_POST['pedido_id'];
    $novo_status = $_POST['novo_status'];
    
    $sqlUpdate = "UPDATE pedidos SET status = ? WHERE id = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    
    if ($stmtUpdate->execute([$novo_status, $pedido_id])) {
        $mensagem = "Status do pedido #$pedido_id atualizado para " . ucfirst($novo_status);
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao atualizar status do pedido";
        $tipoMensagem = "error";
    }
}

// Buscar todos os pedidos
$sqlPedidos = "SELECT p.*, u.nome as usuario_nome, u.email as usuario_email 
               FROM pedidos p 
               JOIN usuarios u ON p.usuario_id = u.id 
               ORDER BY p.data_pedido DESC";
$stmtPedidos = $pdo->query($sqlPedidos);
$pedidos = $stmtPedidos->fetchAll();

// Contadores para filtros
$sqlCounters = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
    SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
    SUM(CASE WHEN status = 'em_preparo' THEN 1 ELSE 0 END) as em_preparo,
    SUM(CASE WHEN status = 'a_caminho' THEN 1 ELSE 0 END) as a_caminho,
    SUM(CASE WHEN status = 'entregue' THEN 1 ELSE 0 END) as entregues,
    SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
    FROM pedidos";
$stmtCounters = $pdo->query($sqlCounters);
$counters = $stmtCounters->fetch();

// Filtro por status
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todos';
if ($filtro_status !== 'todos') {
    $sqlPedidos = "SELECT p.*, u.nome as usuario_nome, u.email as usuario_email 
                   FROM pedidos p 
                   JOIN usuarios u ON p.usuario_id = u.id 
                   WHERE p.status = ?
                   ORDER BY p.data_pedido DESC";
    $stmtPedidos = $pdo->prepare($sqlPedidos);
    $stmtPedidos->execute([$filtro_status]);
    $pedidos = $stmtPedidos->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pedidos - Admin</title>
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
            <img src="../assets/Faustino.png" alt="Logo">
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Main</div>
            <a href="../dashboard.php" class="menu-item ">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="pedidos_dash.php" class="menu-item active">
                <i class="fa-solid fa-clipboard"></i>
                <span>Pedidos</span>
            </a>
            <a href="cardapio_dash.php" class="menu-item">
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
            <a href="relatorios.php" class="menu-item">
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
                <input type="text" placeholder="Buscar pedidos..." id="searchInput">
            </div>

            <div class="button-store">
                <button>
                    <a href="dashboard.php">VER LOJA</a>
                </button>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Gestão de Pedidos</h1>
                <p>Gerencie todos os pedidos do sistema</p>
            </div>

            <!-- Mensagens -->
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtros por Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filtrar por Status</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge filter-badge <?php echo $filtro_status === 'todos' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php'">
                            Todos <span class="badge bg-secondary"><?php echo $counters['total']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'pendente' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=pendente'">
                            Pendente <span class="badge bg-warning"><?php echo $counters['pendentes']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'confirmado' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=confirmado'">
                            Confirmado <span class="badge bg-info"><?php echo $counters['confirmados']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'em_preparo' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=em_preparo'">
                            Em Preparo <span class="badge bg-warning"><?php echo $counters['em_preparo']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'a_caminho' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=a_caminho'">
                            A Caminho <span class="badge bg-primary"><?php echo $counters['a_caminho']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'entregue' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=entregue'">
                            Entregue <span class="badge bg-success"><?php echo $counters['entregues']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'cancelado' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='pedidos.php?status=cancelado'">
                            Cancelado <span class="badge bg-danger"><?php echo $counters['cancelados']; ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Lista de Pedidos -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedidos <?php echo $filtro_status !== 'todos' ? '- ' . ucfirst($filtro_status) : ''; ?></h5>
                    <span class="badge bg-secondary"><?php echo count($pedidos); ?> pedidos</span>
                </div>
                <div class="card-body">
                    <?php if (count($pedidos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="pedidosTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($pedido['usuario_nome']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($pedido['usuario_email']); ?></small>
                                                </div>
                                            </td>
                                            <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $pedido['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo ucfirst($pedido['pagamento']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <!-- Botão para ver detalhes -->
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="verDetalhes(<?php echo $pedido['id']; ?>)"
                                                            title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Dropdown para alterar status -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown"
                                                                title="Alterar Status">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="pendente">
                                                                    <button type="submit" class="dropdown-item <?php echo $pedido['status'] === 'pendente' ? 'active' : ''; ?>">
                                                                        🟡 Pendente
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="confirmado">
                                                                    <button type="submit" class="dropdown-item <?php echo $pedido['status'] === 'confirmado' ? 'active' : ''; ?>">
                                                                        🔵 Confirmado
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="em_preparo">
                                                                    <button type="submit" class="dropdown-item <?php echo $pedido['status'] === 'em_preparo' ? 'active' : ''; ?>">
                                                                        🟠 Em Preparo
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="a_caminho">
                                                                    <button type="submit" class="dropdown-item <?php echo $pedido['status'] === 'a_caminho' ? 'active' : ''; ?>">
                                                                        🚚 A Caminho
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="entregue">
                                                                    <button type="submit" class="dropdown-item <?php echo $pedido['status'] === 'entregue' ? 'active' : ''; ?>">
                                                                        ✅ Entregue
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="cancelado">
                                                                    <button type="submit" class="dropdown-item text-danger <?php echo $pedido['status'] === 'cancelado' ? 'active' : ''; ?>"
                                                                            onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                                                                        ❌ Cancelar
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5>Nenhum pedido encontrado</h5>
                            <p class="text-muted">
                                <?php echo $filtro_status !== 'todos' ? 
                                    'Não há pedidos com status "' . ucfirst($filtro_status) . '"' : 
                                    'Não há pedidos cadastrados no sistema'; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu toggle
        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Busca em tempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#pedidosTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Função para ver detalhes do pedido
        function verDetalhes(pedidoId) {
            alert('Abrindo detalhes do pedido #' + pedidoId + '\n\nEsta funcionalidade pode ser expandida para mostrar:\n- Itens do pedido\n- Endereço de entrega\n- Histórico de status\n- Informações de contato');
            // Aqui você pode implementar um modal ou redirecionar para página de detalhes
            // window.location.href = 'detalhes_pedido.php?id=' + pedidoId;
        }

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