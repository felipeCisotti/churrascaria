<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in relatorios.php. Type: " . gettype($_SESSION));
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

// filtros de data (mesma interface do financeiro)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

$where = " WHERE 1=1 ";
$params = [];
if ($data_inicio) { $where .= " AND data >= ?"; $params[] = $data_inicio; }
if ($data_fim)   { $where .= " AND data <= ?"; $params[] = $data_fim; }

// dados por dia (para line e bar)
$sqlDias = "SELECT data, SUM(total_vendas) AS vendas, SUM(total_pedidos) AS pedidos
            FROM faturamento
            {$where}
            GROUP BY data
            ORDER BY data ASC";
$stmtDias = $pdo->prepare($sqlDias);
$stmtDias->execute($params);
$dias = $stmtDias->fetchAll(PDO::FETCH_ASSOC);

// dados mensais (para doughnut)
$sqlMeses = "SELECT DATE_FORMAT(data, '%Y-%m') AS ym, SUM(total_vendas) AS vendas, SUM(total_pedidos) AS pedidos
             FROM faturamento
             {$where}
             GROUP BY ym
             ORDER BY ym ASC";
$stmtMeses = $pdo->prepare($sqlMeses);
$stmtMeses->execute($params);
$meses = $stmtMeses->fetchAll(PDO::FETCH_ASSOC);

// preparar arrays JS
$labelsDias = [];
$vendasDias = [];
$pedidosDias = [];
foreach ($dias as $r) {
    $labelsDias[] = date('d/m/Y', strtotime($r['data']));
    $vendasDias[] = (float) $r['vendas'];
    $pedidosDias[] = (int) $r['pedidos'];
}

$labelsMeses = [];
$vendasMeses = [];
foreach ($meses as $m) {
    $labelsMeses[] = $m['ym'];
    $vendasMeses[] = (float) $m['vendas'];
}

// estatísticas rápidas
$total_vendas = array_sum($vendasDias);
$total_pedidos = array_sum($pedidosDias);
$media_venda = $total_pedidos ? $total_vendas / $total_pedidos : 0;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .card-stats h3 { margin:0; }
        .chart-wrap { min-height:260px; }
        .app-btn { transition:none !important; }

        /* novo: versão menor para charts auxiliares */
        .chart-wrap.small { min-height:120px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>

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
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="restaurantes.php" class="menu-item">
            <i class="fa-solid fa-location-dot"></i>
                <span>Restaurantes</span>
            </a>
            
                <a href="financeiro.php" class="menu-item">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>Financeiro</span>
            </a>
            <a href=" relatorios.php" class="menu-item active">
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
        <div class="topbar d-flex align-items-center justify-content-between">
            <div class="search-bar"><i class="fas fa-search"></i>
                <input id="searchInput" type="text" placeholder="Buscar..." class="form-control form-control-sm">
            </div>
            <div>
                <a class="btn btn-sm btn-outline-secondary" href="financeiro.php?export=csv"><i class="fas fa-file-csv"></i> Exportar CSV</a>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Relatórios</h1>
                <p>Gráficos de vendas e pedidos (base: tabela faturamento)</p>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-stats p-3">
                        <small class="text-muted">Total Vendas</small>
                        <h3>R$ <?php echo number_format($total_vendas,2,',','.'); ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats p-3">
                        <small class="text-muted">Total Pedidos</small>
                        <h3><?php echo (int)$total_pedidos; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stats p-3">
                        <small class="text-muted">Ticket Médio</small>
                        <h3>R$ <?php echo number_format($media_venda,2,',','.'); ?></h3>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <form class="row g-2 align-items-center" method="GET" action="relatorios.php">
                        <div class="col-auto"><input type="date" name="data_inicio" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data_inicio); ?>"></div>
                        <div class="col-auto"><input type="date" name="data_fim" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data_fim); ?>"></div>
                        <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Aplicar</button></div>
                        <div class="col text-end"><small class="text-muted">Período aplicado</small></div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <div class="card chart-wrap p-3">
                                <h6>Vendas por dia</h6>
                                <canvas id="vendasChart" height="140"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="card chart-wrap p-3">
                                <h6>Pedidos por dia</h6>
                                <canvas id="pedidosChart" height="140"></canvas>
                            </div>
                        </div>
                        <!-- Distribuição mensal centralizada -->
                        <div class="col-lg-6 col-md-8 mx-auto mb-3">
                            <div class="card chart-wrap small p-3">
                                <h6 class="mb-2 text-center">Distribuição mensal de vendas</h6>
                                <canvas id="mesesChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela detalhada -->
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead><tr><th>Data</th><th>Vendas (R$)</th><th>Pedidos</th></tr></thead>
                            <tbody>
                                <?php foreach ($dias as $d): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($d['data'])); ?></td>
                                        <td>R$ <?php echo number_format((float)$d['vendas'],2,',','.'); ?></td>
                                        <td><?php echo (int)$d['pedidos']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($dias)===0): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Nenhum registro para o período</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </main>

<script>
const labelsDias = <?php echo json_encode($labelsDias, JSON_UNESCAPED_UNICODE); ?>;
const vendasDias = <?php echo json_encode($vendasDias); ?>;
const pedidosDias = <?php echo json_encode($pedidosDias); ?>;
const labelsMeses = <?php echo json_encode($labelsMeses, JSON_UNESCAPED_UNICODE); ?>;
const vendasMeses = <?php echo json_encode($vendasMeses); ?>;

function numberFormatBR(value) {
    return value.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

document.addEventListener('DOMContentLoaded', function() {
    const ctxV = document.getElementById('vendasChart').getContext('2d');
    new Chart(ctxV, {
        type: 'line',
        data: {
            labels: labelsDias,
            datasets: [{
                label: 'Vendas (R$)',
                data: vendasDias,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                fill: true,
                tension: 0.2,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: { callbacks: { label: ctx => 'R$ ' + numberFormatBR(ctx.parsed.y) } },
                legend: { display: false }
            },
            scales: {
                y: { ticks: { callback: v => 'R$ ' + Number(v).toLocaleString('pt-BR') } }
            }
        }
    });

    const ctxP = document.getElementById('pedidosChart').getContext('2d');
    new Chart(ctxP, {
        type: 'bar',
        data: {
            labels: labelsDias,
            datasets: [{ label: 'Pedidos', data: pedidosDias, backgroundColor: '#0d6efd' }]
        },
        options: { responsive:true, plugins:{ legend:{display:false} } }
    });

    const ctxM = document.getElementById('mesesChart').getContext('2d');
    new Chart(ctxM, {
        type: 'doughnut',
        data: {
            labels: labelsMeses,
            datasets: [{ data: vendasMeses, backgroundColor: [
                '#2563eb','#0d6efd','#6c757d','#198754','#dc3545','#fd7e14','#20c997'
            ] }]
        },
        options: {
            responsive:true,
            plugins: {
                tooltip: { callbacks: { label: ctx => 'R$ ' + numberFormatBR(ctx.parsed) } },
                legend: { position: 'bottom' }
            }
        }
    });

    // busca local simples
    document.getElementById('searchInput').addEventListener('input', function(e){
        const term = e.target.value.toLowerCase().trim();
        document.querySelectorAll('table tbody tr').forEach(row=>{
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
?>