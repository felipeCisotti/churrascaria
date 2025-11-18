<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$usuario_id = $_SESSION['id'];
$endereco_id = $_POST['endereco_id'] ?? null;
$titulo = trim($_POST['titulo']);
$cep = preg_replace('/[^0-9]/', '', $_POST['cep']);
$logradouro = trim($_POST['logradouro']);
$numero = trim($_POST['numero']);
$complemento = trim($_POST['complemento']);
$bairro = trim($_POST['bairro']);
$cidade = trim($_POST['cidade']);
$estado = trim($_POST['estado']);
$principal = isset($_POST['principal']) ? 1 : 0;

// Validações
if (empty($titulo) || empty($cep) || empty($logradouro) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

try {
    if ($principal) {
        // Remover principal de outros endereços
        $sqlUpdate = "UPDATE enderecos SET principal = 0 WHERE usuario_id = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$usuario_id]);
    }
    
    if ($endereco_id) {
        // Atualizar endereço existente
        $sql = "UPDATE enderecos SET titulo = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, principal = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $principal, $endereco_id, $usuario_id]);
    } else {
        // Inserir novo endereço
        $sql = "INSERT INTO enderecos (usuario_id, titulo, cep, logradouro, numero, complemento, bairro, cidade, estado, principal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $titulo, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $principal]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Endereço salvo com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar endereço: ' . $e->getMessage()]);
}
?>