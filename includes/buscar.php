<?php
include 'conexao.php';

$acao = $_GET['acao'] ?? '';

if ($acao === 'cidades') {
    $estado = $_GET['estado'] ?? '';
    $sql = "SELECT DISTINCT cidade FROM restaurantes WHERE estado = ? ORDER BY cidade";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $estado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $cidades = [];
    while ($row = $resultado->fetch_assoc()) {
        $cidades[] = $row['cidade'];
    }
    echo json_encode($cidades);
}

if ($acao === 'buscar') {
    $estado = $_GET['estado'] ?? '';
    $cidade = $_GET['cidade'] ?? '';

    $sql = "SELECT nome FROM restaurantes WHERE estado = ? AND cidade = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $estado, $cidade);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "<h3>Unidades em $cidade - $estado:</h3><ul>";
        while ($row = $resultado->fetch_assoc()) {
            echo "<li>{$row['nome']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhum restaurante encontrado.</p>";
    }
}
?>
