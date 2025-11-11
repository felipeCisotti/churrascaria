<?php
// includes/cadastrar_restaurante.php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["tipo"] !== "admin") {
    header("location: ../views/login.php");
    exit;
}

include 'connect.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cidade = $_POST['cidade'] ?? '';

    if ($nome && $estado && $cidade) {
        $sql = "INSERT INTO restaurantes (nome, estado, cidade) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nome, $estado, $cidade])) {
            $mensagem = "Restaurante cadastrado com sucesso!";
            $tipoMensagem = "success";
        } else {
            $mensagem = "Erro ao cadastrar restaurante.";
            $tipoMensagem = "error";
        }
    } else {
        $mensagem = "Por favor, preencha todos os campos.";
        $tipoMensagem = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Restaurante - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .cadastro-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .btn-voltar {
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn-voltar:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cadastro-container">
            <h2 class="text-center mb-4">
                <i class="fas fa-plus-circle text-danger"></i>
                Cadastrar Restaurante
            </h2>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipoMensagem === 'success' ? 'success' : 'danger'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do Restaurante</label>
                    <input type="text" class="form-control" id="nome" name="nome" required 
                           placeholder="Ex: DomBrasa Shopping Ibirapuera">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado (UF)</label>
                        <input type="text" class="form-control" id="estado" name="estado" maxlength="2" required 
                               placeholder="Ex: SP" style="text-transform: uppercase;">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="cidade" name="cidade" required 
                               placeholder="Ex: São Paulo">
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2">
                    <i class="fas fa-save"></i> Cadastrar Restaurante
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="../dashboard.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>