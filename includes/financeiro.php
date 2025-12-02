<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in financeiro.php. Type: " . gettype($_SESSION));
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_faturamento'])) {
    $data = $_POST['data'] ?? null;
    $total_vendas = (float) str_replace(',', '.', ($_POST['total_vendas'] ?? 0));
    $total_pedidos = (int) ($_POST['total_pedidos'] ?? 0);

    if ($data) {
        try {
            $sql = "INSERT INTO faturamento (data, total_vendas, total_pedidos) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data, $total_vendas, $total_pedidos]);
            $mensagem = "Registro de faturamento cadastrado.";
            $tipoMensagem = "success";
            header("Location: financeiro.php");
            exit;
        } catch (Exception $e) {
            error_log("Erro inserir faturamento: " . $e->getMessage());
            $mensagem = "Erro ao cadastrar faturamento.";
            $tipoMensagem = "error";
        }
    } else {
        $mensagem = "Data invÃ¡lida.";
        $tipoMensagem = "error";
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_faturamento'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $data = $_POST['data'] ?? null;
    $total_vendas = (float) str_replace(',', '.', ($_POST['total_vendas'] ?? 0));
    $total_pedidos = (int) ($_POST['total_pedidos'] ?? 0);

    if ($id > 0 && $data) {
        try {
            $sql = "UPDATE faturamento SET data = ?, total_vendas = ?, total_pedidos = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data, $total_vendas, $total_pedidos, $id]);
            $mensagem = "Registro atualizado com sucesso.";
            $tipoMensagem = "success";
            header("Location: financeiro.php");
            exit;
        } catch (Exception $e) {
            error_log("Erro atualizar faturamento: " . $e->getMessage());
            $mensagem = "Erro ao atualizar registro.";
            $tipoMensagem = "error";
        }
    } else {
        $mensagem = "Dados invÃ¡lidos para atualizaÃ§Ã£o.";
        $tipoMensagem = "error";
    }
}

if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    if ($id > 0) {
        try {
            $sql = "DELETE FROM faturamento WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $mensagem = "Registro excluÃ­do.";
            $tipoMensagem = "success";
            header("Location: financeiro.php");
            exit;
        } catch (Exception $e) {
            error_log("Erro excluir faturamento: " . $e->getMessage());
            $mensagem = "Erro ao excluir registro.";
            $tipoMensagem = "error";
        }
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sqlExport = "SELECT id, data, total_vendas, total_pedidos FROM faturamento ORDER BY data DESC";
    $stmtExport = $pdo->query($sqlExport);
    $rows = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=faturamento_' . date('Ymd_His') . '.csv');
    $out = fopen('php:
    fputcsv($out, ['id', 'data', 'total_vendas', 'total_pedidos']);
    foreach ($rows as $r)
        fputcsv($out, $r);
    fclose($out);
    exit;
}


$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';


$params = [];
$where = "";
if ($data_inicio) {
    $where .= " AND data >= ?";
    $params[] = $data_inicio;
}
if ($data_fim) {
    $where .= " AND data <= ?";
    $params[] = $data_fim;
}

try {
    $sqlCounters = "SELECT 
        COUNT(*) as registros,
        SUM(total_vendas) as soma_vendas,
        SUM(total_pedidos) as soma_pedidos
        FROM faturamento WHERE 1=1 {$where}";
    $stmtC = $pdo->prepare($sqlCounters);
    $stmtC->execute($params);
    $counters = $stmtC->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT id, data, total_vendas, total_pedidos FROM faturamento WHERE 1=1 {$where} ORDER BY data DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erro buscar faturamento: " . $e->getMessage());
    $counters = ['registros' => 0, 'soma_vendas' => 0, 'soma_pedidos' => 0];
    $registros = [];
}

$total_registros = (int) ($counters['registros'] ?? 0);
$soma_vendas = number_format((float) ($counters['soma_vendas'] ?? 0), 2, ',', '.');
$soma_pedidos = (int) ($counters['soma_pedidos'] ?? 0);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faturamento - Admin</title>
    <link rel="stylesheet" href="https:
    <link href="https:
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .app-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .9rem;
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: none !important;
        }

        .app-btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .app-btn-outline {
            background: transparent;
            color: #0d6efd;
            border-color: rgba(13, 110, 253, 0.12);
        }

        .status-badge {
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        .table .btn {
            transition: none !important;
        }
    </style>
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
                <span>CardÃ¡pio</span>
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
            <a href="financeiro.php" class="menu-item active">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>Financeiro</span>
            </a>
            <a href=" relatorios.php" class="menu-item">
                <i class="fa-solid fa-chart-line"></i>
                <span>RelatÃ³rios</span>
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
            <div class="d-flex gap-2">
                <a class="app-btn app-btn-outline" href="financeiro.php?export=csv"><i class="fas fa-file-csv"></i>
                    Exportar CSV</a>
                <button class="app-btn app-btn-primary" data-bs-toggle="modal" data-bs-target="#modalFaturamento"><i
                        class="fas fa-plus"></i> Novo</button>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Faturamento</h1>
                <p>Registros de faturamento por data</p>
            </div>

            <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo ($tipoMensagem === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show"
                    role="alert">
                    <?php echo $mensagem; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Total de Registros</h6>
                        <h3><?php echo $total_registros; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Soma Vendas</h6>
                        <h3>R$ <?php echo $soma_vendas; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Total Pedidos</h6>
                        <h3><?php echo $soma_pedidos; ?></h3>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <form class="row g-2 align-items-center" method="GET" action="financeiro.php">
                        <div class="col-auto">
                            <input type="date" name="data_inicio" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($data_inicio); ?>">
                        </div>
                        <div class="col-auto">
                            <input type="date" name="data_fim" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($data_fim); ?>">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary" type="submit">Filtrar</button>
                        </div>
                        <div class="col text-end">
                            <small class="text-muted">Total de registros: <?php echo $total_registros; ?></small>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (count($registros) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="faturTable">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Vendas (R$)</th>
                                        <th>Pedidos</th>
                                        <th>AÃ§Ãµes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros as $r): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($r['data'])); ?></td>
                                            <td>R$ <?php echo number_format((float) $r['total_vendas'], 2, ',', '.'); ?></td>
                                            <td><?php echo (int) $r['total_pedidos']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                        onclick="openEditar(<?php echo $r['id']; ?>, '<?php echo $r['data']; ?>', '<?php echo $r['total_vendas']; ?>', '<?php echo $r['total_pedidos']; ?>')"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="confirmarExclusao(<?php echo $r['id']; ?>)" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5>Nenhum registro encontrado</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    
    <div class="modal fade" id="modalFaturamento" tabindex="-1" aria-labelledby="modalFaturamentoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form id="formFatur" method="post" action="financeiro.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFaturamentoLabel">Novo Faturamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="faturId" value="">
                        <div class="mb-2">
                            <label class="form-label">Data</label>
                            <input type="date" name="data" id="faturData" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Total Vendas (R$)</label>
                            <input type="text" name="total_vendas" id="faturVendas" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Total Pedidos</label>
                            <input type="number" name="total_pedidos" id="faturPedidos" class="form-control" min="0"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="app-btn app-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="app-btn app-btn-primary" id="btnSalvar"
                            name="cadastrar_faturamento">Salvar</button>
                        <button type="submit" class="app-btn app-btn-primary d-none" id="btnEditar"
                            name="editar_faturamento">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Confirma exclusÃ£o deste registro?</p>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <a id="deleteConfirmBtn" class="btn btn-danger btn-sm" href="#">Excluir</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https:
    <script>
        
        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        
        document.getElementById('searchInput').addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('#faturTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        function openEditar(id, data, vendas, pedidos) {
            document.getElementById('faturId').value = id;
            document.getElementById('faturData').value = data;
            document.getElementById('faturVendas').value = parseFloat(vendas).toFixed(2).replace('.', ',');
            document.getElementById('faturPedidos').value = pedidos;
            document.getElementById('modalFaturamentoLabel').textContent = 'Editar Faturamento';
            document.getElementById('btnSalvar').classList.add('d-none');
            document.getElementById('btnEditar').classList.remove('d-none');
            new bootstrap.Modal(document.getElementById('modalFaturamento')).show();
        }

        
        document.getElementById('modalFaturamento').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formFatur').reset();
            document.getElementById('faturId').value = '';
            document.getElementById('modalFaturamentoLabel').textContent = 'Novo Faturamento';
            document.getElementById('btnSalvar').classList.remove('d-none');
            document.getElementById('btnEditar').classList.add('d-none');
        });

        function confirmarExclusao(id) {
            document.getElementById('deleteConfirmBtn').href = 'financeiro.php?excluir=' + id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        
        document.getElementById('faturVendas').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9,\.]/g, '').replace(/\./g, ',');
        });
    </script>
</body>

</html>