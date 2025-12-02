<?php
session_start();
include '../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fazer_reserva'])) {
    $usuario_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $data_reserva = $_POST['data_reserva'];
    $horario = $_POST['horario'];
    $qtd_pessoas = $_POST['qtd_pessoas'];

    if (!$usuario_id) {
        $sqlCheckUser = "SELECT id FROM usuarios WHERE email = ?";
        $stmtCheck = $pdo->prepare($sqlCheckUser);
        $stmtCheck->execute([$email]);
        $existingUser = $stmtCheck->fetch();

        if ($existingUser) {
            $usuario_id = $existingUser['id'];
        } else {
            $senha_temporaria = password_hash(uniqid(), PASSWORD_DEFAULT);
            $sqlInsertUser = "INSERT INTO usuarios (nome, email, telefone, senha, tipo) VALUES (?, ?, ?, ?, 'cliente')";
            $stmtUser = $pdo->prepare($sqlInsertUser);
            if ($stmtUser->execute([$nome, $email, $telefone, $senha_temporaria])) {
                $usuario_id = $pdo->lastInsertId();
            }
        }
    }

    if ($usuario_id) {
        $sqlInsert = "INSERT INTO reservas (usuario_id, data_reserva, horario, qtd_pessoas, observacoes) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $observacoes = "Cidade: $cidade, Estado: $estado, Telefone: $telefone";
        
        if ($stmtInsert->execute([$usuario_id, $data_reserva, $horario, $qtd_pessoas, $observacoes])) {
            $mensagem = "Reserva realizada com sucesso! Entraremos em contato para confirmaÃ§Ã£o.";
            $tipoMensagem = "success";
        } else {
            $mensagem = "Erro ao realizar reserva. Tente novamente.";
            $tipoMensagem = "error";
        }
    }
}

$sqlRestaurantes = "SELECT * FROM restaurantes ORDER BY cidade, estado";
$stmtRestaurantes = $pdo->query($sqlRestaurantes);
$restaurantes = $stmtRestaurantes->fetchAll();
?>
<body>

<a href="carrinho.php" class="btn-carrinho-flutuante">
    <i class="fa fa-shopping-cart"></i> 
</a>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="../css/reserva.css">
    <link rel="stylesheet" href="../css/style.css">

    <div class="carroussel-reserva">
    <div id="carouselExampleSlidesOnly" class="carousel slider" data-bs-ride="carousels">
  <div class="carousels-inner">
    <div class="carousels-item active">
      <img src="../assets/img/restaurantes/bannerrest.png" class="d-block w-100" alt="...">
    </div>
</div>
</div>
    </div>

    <div class="subheader">
        <img src="../assets/img/subheader.png" alt="">
    </div>

    <div class="search-mobile">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" placeholder="Buscar carnes, acompanhamentos...">
    <i class="fa-solid fa-filter"></i>
  </div>

  <div class="banner-mob">
    <img src="../assets/img/banner-reserva.png" alt="">
  </div>



    <div class="">
        <?php if (isset($mensagem)): ?>
            <div class="mensagem-reserva <?php echo $tipoMensagem === 'success' ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

            <div class="reserv">
                <div class="reserva-hero">
                    <img src="../assets/img/restaurantes/reserva.png" alt="">
                </div>
            

                <form class="reserva-form" method="POST" action="">
                    <div class="restaurante-info">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cidade">CIDADE</label>
                                <select class="form-control" id="cidade" name="cidade" required>
                                    <option value="">Selecione a cidade</option>
                                    <?php foreach ($restaurantes as $rest): ?>
                                        <option value="<?php echo htmlspecialchars($rest['cidade']); ?>">
                                            <?php echo htmlspecialchars($rest['cidade']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="estado">ESTADO</label>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="">Selecione o estado</option>
                                    <option value="SP">SÃ£o Paulo</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="ES">EspÃ­rito Santo</option>
                                    <option value="PR">ParanÃ¡</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="junto">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_reserva">DIA</label>
                                <input type="date" class="form-control" id="data_reserva" name="data_reserva"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="horario">HORÃRIO</label>
                                <select class="form-control" id="horario" name="horario" required>
                                    <option value="">Selecione o horÃ¡rio</option>
                                    <option value="11:00">11:00</option>
                                    <option value="11:30">11:30</option>
                                    <option value="12:00">12:00</option>
                                    <option value="12:30">12:30</option>
                                    <option value="13:00">13:00</option>
                                    <option value="13:30">13:30</option>
                                    <option value="14:00">14:00</option>
                                    <option value="18:00">18:00</option>
                                    <option value="18:30">18:30</option>
                                    <option value="19:00">19:00</option>
                                    <option value="19:30">19:30</option>
                                    <option value="20:00">20:00</option>
                                    <option value="20:30">20:30</option>
                                    <option value="21:00">21:00</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="qtd_pessoas">PESSOAS</label>
                            <select class="form-control" id="qtd_pessoas" name="qtd_pessoas" required>
                                <option value="">Selecione a quantidade</option>
                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> pessoa<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                                </div>
                    
                    <div class="form-group">
                        <label for="telefone">TELEFONE</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone"
                               placeholder="(11) 99999-9999" required>
                    </div>
                    <div class="form-group">
                        <label for="nome">NOME</label>
                        <input type="text" class="form-control" id="nome" name="nome"
                               value="<?php echo isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : ''; ?>"
                               placeholder="Seu nome completo" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-MAIL</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                               placeholder="seu@email.com" required>
                    </div>
                    <button type="submit" class="btn-reservar" name="fazer_reserva">
                        RESERVAR
                    </button>
                </form>
                        </div>
            </div>

        <div class="infos">
        <div class="row mb-2 inf-res">
            <div class="col-md-4 text-center">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x text-danger mb-3"></i>
                        <h5>HorÃ¡rios de Funcionamento</h5>
                        <p>Segunda a SÃ¡bado: 11h Ã s 23h<br>Domingo: 11h Ã s 17h</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-danger mb-3"></i>
                        <h5>Capacidade</h5>
                        <p>Grupos de atÃ© 20 pessoas<br>Ambiente familiar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card border-0">
                    <div class="card-body">
                        <i class="fas fa-info-circle fa-3x text-danger mb-3"></i>
                        <h5>InformaÃ§Ãµes</h5>
                        <p>Reserva com 2h de antecedÃªncia<br>Cancelamento gratuito</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "churrascaria";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexÃ£o: " . $conn->connect_error);
}

$estados = $conn->query("SELECT DISTINCT estado FROM restaurantes ORDER BY estado");

$cidades = [];
if (isset($_GET['estado']) && $_GET['estado'] !== '') {
    $estadoSelecionado = $conn->real_escape_string($_GET['estado']);
    $cidades = $conn->query("SELECT DISTINCT cidade FROM restaurantes WHERE estado='$estadoSelecionado' ORDER BY cidade");
}

$resultados = [];
if (isset($_GET['buscar'])) {
    $estado = $conn->real_escape_string($_GET['estado']);
    $cidade = $conn->real_escape_string($_GET['cidade']);
    $sql = "SELECT * FROM restaurantes WHERE estado='$estado' AND cidade='$cidade'";
    $resultados = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Localize seu Restaurante</title>
</head>
<body class="">

 <div class="separador mb-1 ">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
        </div>

<div class=" encontre">
    <div class="left">
        <div class="left-tex">
            <h1>Encontre aqui o <span style="color:#f7a01b; font-weight: bold;">Restaurante</span> mais prÃ³ximo!</h1>
            <p>Descubra todas as nossas unidades e encontre a sua preferida.<br> Planeje sua visita e aproveite o momento!</p>
        </div> 
        
        <form class="formul" method="GET">
            <select name="estado" onchange="this.form.submit()">
                <option value="">Estado</option>
                <?php while ($row = $estados->fetch_assoc()) { ?>
                    <option value="<?= $row['estado'] ?>" <?= (isset($_GET['estado']) && $_GET['estado'] == $row['estado']) ? 'selected' : '' ?>>
                        <?= $row['estado'] ?>
                    </option>
                <?php } ?>
            </select>
            
            <select name="cidade">
                <option value="">Cidade</option>
                <?php if ($cidades) while ($row = $cidades->fetch_assoc()) { ?>
                    <option value="<?= $row['cidade'] ?>" <?= (isset($_GET['cidade']) && $_GET['cidade'] == $row['cidade']) ? 'selected' : '' ?>>
                        <?= $row['cidade'] ?>
                    </option>
                <?php } ?>
            </select>
            
            <button class="busc" type="submit" name="buscar">BUSCAR RESTAURANTES</button>
            <button type="button" onclick="abrirNoMaps()" class="btn-local">OU USE SUA LOCALIZAÃ‡ÃƒO ATUAL</button>
        </form>

        <?php if (isset($_GET['buscar'])): ?>
            <div class="resultados">
                <h2>Restaurantes encontrados:</h2>
                <?php if ($resultados->num_rows > 0): ?>
                    <?php while ($r = $resultados->fetch_assoc()): ?>
                        <div class="restaurante">
                            <strong><?= $r['nome'] ?></strong><br>
                            <?= $r['cidade'] ?><br>
                             <?= $r['estado'] ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Nenhum restaurante encontrado nessa regiÃ£o.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="map">
        <img src="../assets/img/mapa.png" alt="Mapa do Brasil">
    </div>
</div>
</body>
</html>


    <script src="https:
    <script>

        function abrirNoMaps() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            const url = `https:

            window.location.href = url;
        }, function(erro) {
            alert("NÃ£o consegui pegar sua localizaÃ§Ã£o.");
        });
    } else {
        alert("Seu navegador nÃ£o suporta geolocalizaÃ§Ã£o.");
    }
}

        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });

        document.getElementById('data_reserva').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
        });

        const cidadeEstadoMap = {
            'SÃ£o Paulo': 'SP',
            'Campinas': 'SP',
            'Rio de Janeiro': 'RJ',
            'NiterÃ³i': 'RJ',
            'Belo Horizonte': 'MG',
            'VitÃ³ria': 'ES',
            'Curitiba': 'PR',
            'FlorianÃ³polis': 'SC',
            'Porto Alegre': 'RS'
        };

        document.getElementById('cidade').addEventListener('change', function() {
            const cidade = this.value;
            const estadoSelect = document.getElementById('estado');
            
            if (cidadeEstadoMap[cidade]) {
                estadoSelect.value = cidadeEstadoMap[cidade];
            }
        }); 

        document.querySelector('form').addEventListener('submit', function(e) {
            const telefone = document.getElementById('telefone').value;
            const telefoneRegex = /^\(\d{2}\) \d{5}-\d{4}$/;
            
            if (!telefoneRegex.test(telefone)) {
                e.preventDefault();
                alert('Por favor, insira um telefone vÃ¡lido no formato (11) 99999-9999');
                return false;
            }
        });
    </script>
</body>
</html>

    <?php include '../includes/footer.php'; ?>
