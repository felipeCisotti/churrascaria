<?php
session_start();

// ajuste o caminho do include conforme sua estrutura
// se seu connect.php estiver em includes/connect.php use '../includes/connect.php'
include 'connect.php'; // ou include '../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedidos.php');
    exit;
}

// validações básicas
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    header('Location: pedidos.php?erro=parametros');
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
$status = $_POST['status'];

if ($id === false) {
    header('Location: pedidos.php?erro=id_invalido');
    exit;
}

$statusValidos = [
    'pendente',
    'confirmado',
    'em_preparo',
    'a_caminho',
    'entregue',
    'cancelado'
];

if (!in_array($status, $statusValidos, true)) {
    header('Location: pedidos.php?erro=status_invalido');
    exit;
}

// atualiza no banco usando $pdo (substitua se sua variável de conexão for diferente)
try {
    // Verifica se $pdo existe
    if (!isset($pdo) || !$pdo) {
        // alternativa: se seu connect.php define $conn, troque $pdo por $conn abaixo
        throw new Exception('Conexão com o banco não encontrada (variável $pdo ausente).');
    }

    $sql = "UPDATE pedidos SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // redireciona de volta para a página anterior, se disponível
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
        header('Location: ' . $back . '?sucesso=1');
        exit;
    } else {
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
        header('Location: ' . $back . '?erro=banco');
        exit;
    }
} catch (Exception $e) {
    // log do erro para debug (opcional)
    error_log('Erro atualizar_status.php: ' . $e->getMessage());
    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
    header('Location: ' . $back . '?erro=exception');
    exit;
}
