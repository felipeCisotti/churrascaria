<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_array($_SESSION)) {
    error_log("$_SESSION is not an array in reservas.php. Type: " . gettype($_SESSION));
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

// Processar nova reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_reserva'])) {
    $usuario_id = $_POST['usuario_id'];
    $data_reserva = $_POST['data_reserva'];
    $horario = $_POST['horario'];
    $qtd_pessoas = $_POST['qtd_pessoas'];
    $observacoes = $_POST['observacoes'] ?? '';

    $sqlInsert = "INSERT INTO reservas (usuario_id, data_reserva, horario, qtd_pessoas, observacoes) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    
    if ($stmtInsert->execute([$usuario_id, $data_reserva, $horario, $qtd_pessoas, $observacoes])) {
        $mensagem = "Reserva cadastrada com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao cadastrar reserva";
        $tipoMensagem = "error";
    }
}

// Processar atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $reserva_id = $_POST['reserva_id'];
    $novo_status = $_POST['novo_status'];
    
    $sqlUpdate = "UPDATE reservas SET status = ? WHERE id = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    
    if ($stmtUpdate->execute([$novo_status, $reserva_id])) {
        $mensagem = "Status da reserva #$reserva_id atualizado para " . ucfirst($novo_status);
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao atualizar status da reserva";
        $tipoMensagem = "error";
    }
}

// Processar exclusão de reserva
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    
    $sqlDelete = "DELETE FROM reservas WHERE id = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    
    if ($stmtDelete->execute([$id])) {
        $mensagem = "Reserva excluída com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao excluir reserva";
        $tipoMensagem = "error";
    }
}

// Buscar todas as reservas
$sqlReservas = "SELECT r.*, u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone 
                FROM reservas r 
                JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.data_reserva DESC, r.horario DESC";
$stmtReservas = $pdo->query($sqlReservas);
$reservas = $stmtReservas->fetchAll();

// Buscar usuários para o select
$sqlUsuarios = "SELECT id, nome, email FROM usuarios ORDER BY nome";
$stmtUsuarios = $pdo->query($sqlUsuarios);
$usuarios = $stmtUsuarios->fetchAll();

// Contadores
$sqlCounters = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
    SUM(CASE WHEN status = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as canceladas
    FROM reservas";
$stmtCounters = $pdo->query($sqlCounters);
$counters = $stmtCounters->fetch();

// Filtro por status
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todas';
if ($filtro_status !== 'todas') {
    $sqlReservas = "SELECT r.*, u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone 
                    FROM reservas r 
                    JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.status = ?
                    ORDER BY r.data_reserva DESC, r.horario DESC";
    $stmtReservas = $pdo->prepare($sqlReservas);
    $stmtReservas->execute([$filtro_status]);
    $reservas = $stmtReservas->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Reservas - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .app-btn-success {
            background: #198754;
            color: white;
        }

        .app-btn-warning {
            background: #ffc107;
            color: #000;
        }

        .app-btn-sm { padding: 6px 12px; font-size: 0.875rem; }
        .app-btn-lg { padding: 12px 20px; font-size: 1.1rem; }

        .btn, .app-btn, .card-footer .btn, .btn-group .btn {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: none !important;
            transition: none !important;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-pendente {
            background: #ffc107;
            color: #000;
        }

        .status-confirmada {
            background: #198754;
            color: white;
        }

        .status-cancelada {
            background: #dc3545;
            color: white;
        }

        .table td {
            vertical-align: middle;
        }

        .reserva-card {
            border-left: 4px solid;
            transition: all 0.2s ease;
        }

        .reserva-pendente {
            border-left-color: #ffc107;
        }

        .reserva-confirmada {
            border-left-color: #198754;
        }

        .reserva-cancelada {
            border-left-color: #dc3545;
        }

        .calendar-icon {
            color: #9a0000ff;
        }

        .deta:hover{
            background-color: #e64f4fa7;
        }

        .dropdown-toggle:hover{
            background-color: #e64f4fa7;
        }

        .exc:hover{
            background-color: #e64f4fa7;
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
            <a href="../dashboard.php" class="menu-item">
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
            <a href="includes/reservas.php" class="menu-item">
                <i class="fa-solid fa-pen"></i>
                <span>Reservas</span>
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
                <input type="text" placeholder="Buscar reservas..." id="searchInput">
            </div>

            <div class="button-store">
                <button class="app-btn app-btn-primary" data-bs-toggle="modal" data-bs-target="#modalReserva">
                    <i class="fas fa-plus"></i>
                    <span>Nova Reserva</span>
                </button>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="content-header">
                <h1>Gestão de Reservas</h1>
                <p>Gerencie as reservas do restaurante</p>
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
                        <div class="card-body text-center">
                            <h3><?php echo $counters['total']; ?></h3>
                            <p class="text-muted">Total de Reservas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-warning"><?php echo $counters['pendentes']; ?></h3>
                            <p class="text-muted">Pendentes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-success"><?php echo $counters['confirmadas']; ?></h3>
                            <p class="text-muted">Confirmadas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-danger"><?php echo $counters['canceladas']; ?></h3>
                            <p class="text-muted">Canceladas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros por Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Filtrar por Status</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge filter-badge <?php echo $filtro_status === 'todas' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='reservas.php'">
                            Todas <span class="badge bg-secondary"><?php echo $counters['total']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'pendente' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='reservas.php?status=pendente'">
                            Pendente <span class="badge bg-warning"><?php echo $counters['pendentes']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'confirmada' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='reservas.php?status=confirmada'">
                            Confirmada <span class="badge bg-success"><?php echo $counters['confirmadas']; ?></span>
                        </span>
                        <span class="badge filter-badge <?php echo $filtro_status === 'cancelada' ? 'bg-primary active' : 'bg-light text-dark'; ?>" 
                              onclick="window.location.href='reservas.php?status=cancelada'">
                            Cancelada <span class="badge bg-danger"><?php echo $counters['canceladas']; ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Lista de Reservas -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Reservas <?php echo $filtro_status !== 'todas' ? '- ' . ucfirst($filtro_status) : ''; ?></h5>
                    <span class="badge bg-secondary"><?php echo count($reservas); ?> reservas</span>
                </div>
                <div class="card-body">
                    <?php if (count($reservas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reservasTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Pessoas</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas as $reserva): ?>
                                        <tr class="reserva-card reserva-<?php echo $reserva['status']; ?>">
                                            <td><strong>#<?php echo $reserva['id']; ?></strong></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($reserva['usuario_nome']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($reserva['usuario_email']); ?></small>
                                                    <?php if ($reserva['usuario_telefone']): ?>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($reserva['usuario_telefone']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar calendar-icon me-1"></i>
                                                <?php echo date('d/m/Y', strtotime($reserva['data_reserva'])); ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-clock calendar-icon me-1"></i>
                                                <?php echo date('H:i', strtotime($reserva['horario'])); ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-users calendar-icon me-1"></i>
                                                <?php echo $reserva['qtd_pessoas']; ?> pessoa(s)
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $reserva['status']; ?>">
                                                    <?php echo ucfirst($reserva['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div  style=" background-color: #ab0000ff; .btn-group::hover: background-color: #ff8b8ba4" class="btn-group">
                                                    <!-- Botão para ver detalhes -->
                                                    <button class="deta btn btn-sm btn-outline-primary" 
                                                            onclick="verDetalhes(<?php echo $reserva['id']; ?>)"
                                                            title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Dropdown para alterar status -->
                                                    <div  style=" background-color: #ab0000ff;" class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown"
                                                                title="Alterar Status">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="pendente">
                                                                    <input type="hidden" name="atualizar_status" value="1">
                                                                    <button style="color: #ebbc00ff;" type="submit" class="dropdown-item <?php echo $reserva['status'] === 'pendente' ? 'active' : ''; ?>">
                                                                         Pendente
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="confirmada">
                                                                    <input type="hidden" name="atualizar_status" value="1">
                                                                    <button style="color: #019b07ff;" type="submit" class="dropdown-item <?php echo $reserva['status'] === 'confirmada' ? 'active' : ''; ?>">
                                                                        Confirmada
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li><hr style=".dropdown-divider::hover: background-color: #ff8b8ba4" class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                                    <input type="hidden" name="novo_status" value="cancelada">
                                                                    <input type="hidden" name="atualizar_status" value="1">
                                                                    <button style="color: #9b0101ff;" type="submit" class="dropdown-item text-danger <?php echo $reserva['status'] === 'cancelada' ? 'active' : ''; ?>"
                                                                            onclick="return confirm('Tem certeza que deseja cancelar esta reserva?')">
                                                                         Cancelada
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    
                                                    <!-- Botão para excluir -->
                                                    <button class="exc btn btn-sm btn-danger" 
                                                            onclick="confirmarExclusao(<?php echo $reserva['id']; ?>, '<?php echo htmlspecialchars($reserva['usuario_nome']); ?>')"
                                                            title="Excluir">
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
                            <i class="fas fa-calendar-days fa-3x text-muted mb-3"></i>
                            <h5>Nenhuma reserva encontrada</h5>
                            <p class="text-muted">
                                <?php echo $filtro_status !== 'todas' ? 
                                    'Não há reservas com status "' . ucfirst($filtro_status) . '"' : 
                                    'Não há reservas cadastradas no sistema'; ?>
                            </p>
                            <button class="app-btn app-btn-primary" data-bs-toggle="modal" data-bs-target="#modalReserva">
                                <i class="fas fa-plus"></i> Fazer Primeira Reserva
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Nova Reserva -->
    <div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReservaLabel">Nova Reserva</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Cliente</label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required>
                                <option value="">Selecione um cliente</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo htmlspecialchars($usuario['nome']); ?> (<?php echo htmlspecialchars($usuario['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_reserva" class="form-label">Data</label>
                                <input type="date" class="form-control" id="data_reserva" name="data_reserva" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="horario" class="form-label">Horário</label>
                                <input type="time" class="form-control" id="horario" name="horario" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="qtd_pessoas" class="form-label">Quantidade de Pessoas</label>
                            <input type="number" class="form-control" id="qtd_pessoas" name="qtd_pessoas" min="1" max="20" required>
                        </div>
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Observações especiais, alergias, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="app-btn app-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="app-btn app-btn-primary" name="cadastrar_reserva">
                            <i class="fas fa-save"></i> Fazer Reserva
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
                    <p>Tem certeza que deseja excluir a reserva de <strong id="deleteReservaNome"></strong>?</p>
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
                const nome = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(2) small').textContent.toLowerCase();
                const data = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const horario = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                const matches = nome.includes(searchTerm) || 
                               email.includes(searchTerm) || 
                               data.includes(searchTerm) || 
                               horario.includes(searchTerm);
                
                row.style.display = matches ? '' : 'none';
            });
        });

        // Confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('deleteReservaNome').textContent = nome;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.dataset.id = id;
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const id = this.dataset.id;
            window.location.href = `reservas.php?excluir=${id}`;
        });

        // Função para ver detalhes da reserva
        function verDetalhes(reservaId) {
            alert('Abrindo detalhes da reserva #' + reservaId + '\n\nEsta funcionalidade pode ser expandida para mostrar:\n- Informações completas do cliente\n- Histórico de reservas\n- Observações especiais');
        }

        // Limpar campos do modal ao fechar
        document.getElementById('modalReserva').addEventListener('hidden.bs.modal', function () {
            this.querySelector('form').reset();
        });

        // Validação da data (não permitir datas passadas)
        document.getElementById('data_reserva').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Não é possível fazer reservas para datas passadas.');
                this.value = '';
            }
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