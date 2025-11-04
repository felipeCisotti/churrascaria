<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in dashboard.php. Type: " . gettype($_SESSION));
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

include ("includes/connect.php");

$userName = isset($_SESSION["nome"]) ? htmlspecialchars($_SESSION["nome"]) : "Admin";

// Consultas existentes
$sqlUsers = "SELECT COUNT(*) FROM usuarios";
$stmtUsers = $pdo->query($sqlUsers);
$totalUser = $stmtUsers->fetchColumn();

$sqlProdutos = "SELECT COUNT(*) FROM produtos";
$stmtProdutos = $pdo->query($sqlProdutos);
$totalProdutos = $stmtProdutos->fetchColumn();

$sqlPedidos = "SELECT COUNT(*) FROM pedidos";
$stmtPedidos = $pdo->query($sqlPedidos);
$totalPedidos = $stmtPedidos->fetchColumn();

// NOVAS CONSULTAS PARA AVALIAÇÕES E FATURAMENTO
// Faturamento de hoje
$sqlFaturamentoHoje = "SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE DATE(data_pedido) = CURDATE() AND status = 'entregue'";
$stmtFaturamento = $pdo->query($sqlFaturamentoHoje);
$faturamentoHoje = $stmtFaturamento->fetchColumn();

// Média de avaliações
$sqlMediaAvaliacoes = "SELECT COALESCE(AVG(avaliacao_media), 0) FROM produtos WHERE avaliacao_media > 0";
$stmtMedia = $pdo->query($sqlMediaAvaliacoes);
$mediaAvaliacoes = $stmtMedia->fetchColumn();

// Pedidos de hoje
$sqlPedidosHoje = "SELECT COUNT(*) FROM pedidos WHERE DATE(data_pedido) = CURDATE()";
$stmtPedidosHoje = $pdo->query($sqlPedidosHoje);
$pedidosHoje = $stmtPedidosHoje->fetchColumn();

// Total de avaliações
$sqlTotalAvaliacoes = "SELECT COUNT(*) FROM avaliacoes";
$stmtTotalAval = $pdo->query($sqlTotalAvaliacoes);
$totalAvaliacoes = $stmtTotalAval->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/img/3.png" alt="Logo">
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Main</div>
            <a href="#" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-label">Actions</div>
            <a href="includes/pedidos_dash.php" class="menu-item">
                <i class="fa-solid fa-clipboard"></i>
                <span>Pedidos</span>
            </a>
            <a href="includes/cardapio_dash.php" class="menu-item">
                <i class="fa-solid fa-utensils"></i>
                <span>Cardápio</span>
            </a>
            <a href="includes/clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="includes/financeiro.php" class="menu-item">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>Financeiro</span>
            </a>
            <a href="includes/relatorios.php" class="menu-item">
                <i class="fa-solid fa-chart-line"></i>
                <span>Relatórios</span>
            </a>

            <div class="menu-label">Logout</div>
            <a href="includes/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="search-bar" id="search-bar">
                <i class="fas fa-search"></i>
                <input type="text">
            </div>

            <div class="button-store" id="button">
                <button>
                    <a href="">VER LOJA</a>
                </button>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Dashboard</h1>
                <p>Visão geral do restaurante</p>
            </div>

            <div class="card-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pedidos Hoje</h3>
                        <div class="card-icon" style="background-color: #BB1600;">
                            <i class="fa-regular fa-clipboard"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $pedidosHoje ?></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Faturamento Hoje</h3>
                        <div class="card-icon" style="background-color: #BB1600;">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">R$ <?php echo number_format($faturamentoHoje, 2, ',', '.') ?></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Clientes Ativos</h3>
                        <div class="card-icon" style="background-color: #BB1600;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $totalUser ?></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Avaliação Média</h3>
                        <div class="card-icon" style="background-color: #BB1600;">
                            <i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo number_format($mediaAvaliacoes, 1) ?>/5.0</div>
                </div>
            </div>

            <!-- NOVA SEÇÃO PARA AVALIAÇÕES RECENTES -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Avaliações Recentes</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $sqlAvaliacoes = "SELECT a.*, p.nome as produto_nome, u.nome as usuario_nome 
                                            FROM avaliacoes a 
                                            JOIN produtos p ON a.produto_id = p.id 
                                            JOIN usuarios u ON a.usuario_id = u.id 
                                            ORDER BY a.data_avaliacao DESC 
                                            LIMIT 5";
                            $stmtAvaliacoes = $pdo->query($sqlAvaliacoes);
                            $avaliacoes = $stmtAvaliacoes->fetchAll();
                            
                            if (count($avaliacoes) > 0) {
                                foreach ($avaliacoes as $avaliacao) {
                                    echo '<div class="avaliacao-item mb-3 p-2 border-bottom">';
                                    echo '<div class="d-flex justify-content-between">';
                                    echo '<strong>' . htmlspecialchars($avaliacao['produto_nome']) . '</strong>';
                                    echo '<span class="badge bg-warning">' . $avaliacao['nota'] . '/5</span>';
                                    echo '</div>';
                                    echo '<small class="text-muted">Por: ' . htmlspecialchars($avaliacao['usuario_nome']) . '</small>';
                                    if (!empty($avaliacao['comentario'])) {
                                        echo '<p class="mb-0 mt-1"><em>"' . htmlspecialchars($avaliacao['comentario']) . '"</em></p>';
                                    }
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-muted">Nenhuma avaliação encontrada.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Produtos Mais Bem Avaliados</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $sqlTopProdutos = "SELECT nome, preco, avaliacao_media 
                                             FROM produtos 
                                             WHERE avaliacao_media > 0 
                                             ORDER BY avaliacao_media DESC 
                                             LIMIT 5";
                            $stmtTopProdutos = $pdo->query($sqlTopProdutos);
                            $topProdutos = $stmtTopProdutos->fetchAll();
                            
                            if (count($topProdutos) > 0) {
                                foreach ($topProdutos as $produto) {
                                    echo '<div class="produto-item mb-2 p-2 border-bottom">';
                                    echo '<div class="d-flex justify-content-between">';
                                    echo '<span>' . htmlspecialchars($produto['nome']) . '</span>';
                                    echo '<span class="badge bg-success">' . number_format($produto['avaliacao_media'], 1) . '/5</span>';
                                    echo '</div>';
                                    echo '<small class="text-muted">R$ ' . number_format($produto['preco'], 2, ',', '.') . '</small>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-muted">Nenhum produto avaliado ainda.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO DE PEDIDOS (EXISTENTE) -->
            <div class="todos-pedidos mt-4">
                <section class="ped-list">
                    <h3>Pedidos</h3>
                    <?php
                    $sqlAll = "SELECT p.id, u.nome as usuario_nome, p.total, p.status, p.data_pedido 
                              FROM pedidos p 
                              JOIN usuarios u ON p.usuario_id = u.id 
                              ORDER BY p.data_pedido DESC 
                              LIMIT 10";
                    $resAll = $pdo->query($sqlAll);
                    $pedidos = $resAll->fetchAll();
                    
                    if (count($pedidos) > 0) {
                        echo "<table class='ped-table table table-striped'>";
                        echo "<thead><tr><th>ID</th><th>Usuário</th><th>Total</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($pedidos as $pedido) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($pedido['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($pedido['usuario_nome']) . "</td>";
                            echo "<td>R$ " . number_format($pedido['total'], 2, ',', '.') . "</td>";
                            echo "<td>
                                    <form method='POST' action='atualizar_status.php' class='d-inline'>
                                        <input type='hidden' name='id' value='" . htmlspecialchars($pedido['id']) . "'>
                                        <select name='status' class='form-select form-select-sm' onchange='this.form.submit()'>
                                            <option value='pendente'" . ($pedido['status'] == 'pendente' ? ' selected' : '') . ">Pendente</option>
                                            <option value='confirmado'" . ($pedido['status'] == 'confirmado' ? ' selected' : '') . ">Confirmado</option>
                                            <option value='em_preparo'" . ($pedido['status'] == 'em_preparo' ? ' selected' : '') . ">Em Preparo</option>
                                            <option value='a_caminho'" . ($pedido['status'] == 'a_caminho' ? ' selected' : '') . ">A Caminho</option>
                                            <option value='entregue'" . ($pedido['status'] == 'entregue' ? ' selected' : '') . ">Entregue</option>
                                            <option value='cancelado'" . ($pedido['status'] == 'cancelado' ? ' selected' : '') . ">Cancelado</option>
                                        </select>
                                    </form>
                                  </td>";
                            echo "<td>" . htmlspecialchars($pedido['data_pedido']) . "</td>";
                            echo "<td>
                                    <form method='POST' action='delete_pedido.php' class='d-inline'>
                                        <input type='hidden' name='id' value='" . htmlspecialchars($pedido['id']) . "'>
                                        <button type='submit' class='btn btn-danger btn-sm' onclick=\"return confirm('Tem certeza?')\">Deletar</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p>Nenhum Pedido encontrado.</p>";
                    }
                    ?>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Seus scripts existentes...
        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

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