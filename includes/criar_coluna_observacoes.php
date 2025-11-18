<?php
require_once "connect.php";

try {
    // Verificar se a coluna já existe
    $sqlCheck = "SHOW COLUMNS FROM pedidos LIKE 'observacoes'";
    $stmtCheck = $pdo->query($sqlCheck);
    $colunaExiste = $stmtCheck->fetch();
    
    if (!$colunaExiste) {
        // Adicionar coluna observacoes
        $sqlAlter = "ALTER TABLE pedidos ADD COLUMN observacoes TEXT NULL AFTER endereco_entrega_id";
        $pdo->exec($sqlAlter);
        echo "Coluna 'observacoes' adicionada com sucesso!";
    } else {
        echo "Coluna 'observacoes' já existe.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>