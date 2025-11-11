<?php
include("../includes/connect.php");
session_start();

// Se não estiver logado, redireciona
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];

// Buscar informações do usuário
$sqlUser = "SELECT nome, email FROM usuarios WHERE id = ?";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([$usuario_id]);
$p = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Buscar o pedido mais recente
$sqlUltimoPedido = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC LIMIT 1";
$stmtUltimo = $pdo->prepare($sqlUltimoPedido);
$stmtUltimo->execute([$usuario_id]);
$ultimoPedido = $stmtUltimo->fetch(PDO::FETCH_ASSOC);

// Buscar histórico completo
$sqlPedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC";
$stmtPedidos = $pdo->prepare($sqlPedidos);
$stmtPedidos->execute([$usuario_id]);
$pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil</title>
    <style>
        <style> :root {
            --primary-color: #4a6fa5;
            --secondary-color: #6b8cbc;
            --accent-color: #ff6b6b;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-page {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .profile-sidebar {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            height: fit-content;
        }

        .profile-picture {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-color);
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .change-photo-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .change-photo-btn:hover {
            background-color: rgba(0, 0, 0, 0.9);
        }

        .user-details {
            text-align: center;
            margin-bottom: 25px;
        }

        .user-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-email {
            color: #6c757d;
            font-size: 14px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            border-radius: var(--border-radius);
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #e9ecef;
        }

        .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: white;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #e05555;
        }

        .profile-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
        }

        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .orders-list {
            display: grid;
            gap: 20px;
        }

        .order-card {
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 20px;
            transition: box-shadow 0.3s;
        }

        .order-card:hover {
            box-shadow: var(--box-shadow);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .order-id {
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-date {
            color: #6c757d;
            font-size: 14px;
        }

        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-items {
            margin-bottom: 15px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f1f1;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 18px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
        }

        .recent-order-section {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
        }

        .reorder-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .reorder-btn:hover {
            background-color: #e05555;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-title {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .photo-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .photo-option {
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
        }

        .photo-option:hover {
            background-color: #f8f9fa;
        }

        #fileInput {
            display: none;
        }

        @media (max-width: 768px) {
            .profile-page {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                margin-bottom: 20px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
    </style>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <div class="profile-page">
            <div class="profile-sidebar">
                <div class="profile-picture">
                    <img id="profileImage"
                        src="<?php echo !empty($p['foto']) ? '../uploads/' . $p['foto'] : 'https://via.placeholder.com/150'; ?>"
                        alt="Foto do perfil">
                    <div class="change-photo-btn" id="changePhotoBtn">Alterar foto</div>
                </div>

                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($p['nome']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($p['email']); ?></div>
                </div>

                <ul class="sidebar-menu">
                    <li><a href="#" class="active">Meus Pedidos</a></li>
                    <li><a href="#">Meus Dados</a></li>
                    <li><a href="#">Endereços</a></li>
                    <li><a href="#">Cartões</a></li>
                    <li><a href="#">Cupons</a></li>
                </ul>

                <form action="../includes/logout.php" method="POST">
                    <button class="logout-btn">Sair</button>
                </form>
            </div>

            <div class="profile-content">
                <h2 class="section-title">Meus Pedidos</h2>

                <div class="recent-order-section">
                    <h3>Pedido Mais Recente</h3>
                    <?php if ($ultimoPedido): ?>
                        <?php
                        $sqlItens = "SELECT * FROM itens_pedido WHERE pedido_id = ?";
                        $stmtItens = $pdo->prepare($sqlItens);
                        $stmtItens->execute([$ultimoPedido['id']]);
                        $itensUltimo = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">Pedido #<?php echo $ultimoPedido['id']; ?></div>
                                <div class="order-date">
                                    <?php echo date('d/m/Y H:i', strtotime($ultimoPedido['data_pedido'])); ?>
                                </div>
                            </div>
                            <div class="order-status status-<?php echo strtolower($ultimoPedido['status']); ?>">
                                <?php echo ucfirst($ultimoPedido['status']); ?>
                            </div>

                            <div class="order-items">
                                <?php foreach ($itensUltimo as $item): ?>
                                    <div class="order-item">
                                        <span><?php echo htmlspecialchars($item['produto']); ?></span>
                                        <span>
                                            R$
                                            <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-total">
                                <span>Total:</span>
                                <span>R$ <?php echo number_format($ultimoPedido['total'], 2, ',', '.'); ?></span>
                            </div>

                            <button class="reorder-btn" id="reorderBtn">Refazer este pedido</button>
                        </div>
                    <?php else: ?>
                        <p>Você ainda não fez nenhum pedido.</p>
                    <?php endif; ?>
                </div>

                <h3>Histórico de Pedidos</h3>
                <div class="orders-list">
                    <?php if ($pedidos): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <?php
                            $sqlItens = "SELECT * FROM itens_pedido WHERE pedido_id = ?";
                            $stmtItens = $pdo->prepare($sqlItens);
                            $stmtItens->execute([$pedido['id']]);
                            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">Pedido #<?php echo $pedido['id']; ?></div>
                                    <div class="order-date">
                                        <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                                    </div>
                                </div>
                                <div class="order-status status-<?php echo strtolower($pedido['status']); ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </div>

                                <div class="order-items">
                                    <?php foreach ($itens as $item): ?>
                                        <div class="order-item">
                                            <span><?php echo htmlspecialchars($item['produto']); ?></span>
                                            <span>
                                                R$
                                                <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="order-total">
                                    <span>Total:</span>
                                    <span>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></span>
                                </div>

                                <div class="order-actions">
                                    <button class="btn btn-primary">Ver detalhes</button>
                                    <button class="btn btn-outline">Refazer pedido</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Você ainda não possui pedidos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais e JS -->
    <?php include("modals.php"); ?>
    <script src="perfil.js"></script>
</body>

</html>