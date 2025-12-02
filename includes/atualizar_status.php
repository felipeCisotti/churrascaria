<?php
session_start();

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedidos.php');
    exit;
}

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

try {
    if (!isset($pdo) || !$pdo) {
        throw new Exception('ConexÃ£o com o banco nÃ£o encontrada (variÃ¡vel $pdo ausente).');
    }

    $sql = "UPDATE pedidos SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
        header('Location: ' . $back . '?sucesso=1');
        exit;
    } else {
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
        header('Location: ' . $back . '?erro=banco');
        exit;
    }
} catch (Exception $e) {
    error_log('Erro atualizar_status.php: ' . $e->getMessage());
    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
    header('Location: ' . $back . '?erro=exception');
    exit;
}
