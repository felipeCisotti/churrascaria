<?php
require_once "connect.php";

try {
    
    $sqlCheck = "SHOW COLUMNS FROM pedidos LIKE 'observacoes'";
    $stmtCheck = $pdo->query($sqlCheck);
    $colunaExiste = $stmtCheck->fetch();
    
    if (!$colunaExiste) {
        
        $sqlAlter = "ALTER TABLE pedidos ADD COLUMN observacoes TEXT NULL AFTER endereco_entrega_id";
        $pdo->exec($sqlAlter);
        echo "Coluna 'observacoes' adicionada com sucesso!";
    } else {
        echo "Coluna 'observacoes' jÃ¡ existe.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>