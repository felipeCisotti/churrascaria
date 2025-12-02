<?php
    include 'connect.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: pedidos.php');
        exit;
    }
    if (!isset($_POST['id'])) {
        header('Location: pedidos.php?erro=parametros');
        exit;
    }
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        header('Location: pedidos.php?erro=id_invalido');
        exit;
    }
    try {
        if (!isset($pdo) || !$pdo) {
            throw new Exception('ConexÃ£o com o banco nÃ£o encontrada (variÃ¡vel $pdo ausente).');
        }

        $sql = "DELETE FROM pedidos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
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
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'pedidos.php';
        header('Location: ' . $back . '?erro=excecao');
        exit;
    }
?>