

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <link rel="stylesheet" href="../css/style.css">
        

</head>
<body>
  <div class="desktop">

<header class="header">
  <div class="header-container">
    <div class="logo">
      <img src="../assets/img/1.png" alt="DomBrasa">
    </div>

    <div class="search-box">
      <input type="text" placeholder="Pesquisar">
      <button><i class="fa fa-search"></i></button>
    </div>

    <nav class="nav-menu">
      <a href="index.php"><i class="fa fa-home"></i> Início</a>
      <a href="cardapio.php "><i class="fa fa-book"></i> Cardápio</a>
      <a href="reserva.php"><i class="fa fa-map-marker-alt"></i> Restaurantes</a>
      <a href="#valores"><i class="fa fa-info-circle"></i> Sobre Nós</a>
      <a href="perfil.php"><i class="fa fa-user"></i> Perfil</a>
      <a href="carrinho.php" class="carrinho-link">
    <i class="fa fa-shopping-cart"></i> Carrinho
    <?php 
    $total_itens = 0;
    if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
        $total_itens = array_sum($_SESSION['carrinho']);
    }
    if ($total_itens > 0): ?>
        <span class="carrinho-count"><?php echo $total_itens; ?></span>
    <?php endif; ?>
</a>
    </nav>
  </div>
</header>
</div>

<div class="mobile">

<header class="header-mob">
  <div class="header-container-mob">
    <div class="logo-mob">
      <img src="../assets/img/2 1.png" alt="DomBrasa">
    </div> 
    <div class="itens">
      <a href="perfil.php"><i class="fa fa-user"></i></a>
    </div>
  

    <!-- <nav class="nav-menu">
      <a href="index.php"><i class="fa fa-home"></i> Início</a>
      <a href="cardapio.php "><i class="fa fa-book"></i> Cardápio</a>
      <a href="reserva.php"><i class="fa fa-map-marker-alt"></i> Restaurantes</a>
      <a href="#valores"><i class="fa fa-info-circle"></i> Sobre Nós</a>
      <a href="perfil.php"><i class="fa fa-user"></i> Perfil</a>
      <a href="carrinho.php" class="carrinho-link">
    <i class="fa fa-shopping-cart"></i> Carrinho-->
     <!-- #region --><!--
</a>
    </nav>-->
  </div>

  <div class="mob-bottom">
    <div class="itens-bottom">
      <a href="index.php"><i class="fa fa-home"></i></a>
      <a href="reserva.php"><i class="fa fa-map-marker-alt"></i></a>
      <a href="#valores"><i class="fa fa-info-circle"></i></a>
      <a href="menu.html"><i class="fa fa-bars" aria-hidden="true"></i></a>
    </div>
  </div>
</header>
</div>

<script>

  function atualizarContadorCarrinho() {
      fetch('../includes/contador_carrinho.php')
          .then(response => response.json())
          .then(data => {
              const contador = document.querySelector('.carrinho-count');
              const carrinhoLink = document.querySelector('.carrinho-link');
  
              if (data.total_itens > 0) {
                  if (!contador) {
                      // Criar contador se não existir
                      const novoContador = document.createElement('span');
                      novoContador.className = 'carrinho-count';
                      novoContador.textContent = data.total_itens;
                      carrinhoLink.appendChild(novoContador);
                  } else {
                      contador.textContent = data.total_itens;
                  }
              } else if (contador) {
                  contador.remove();
              }
          });
  }
  
  // Chamar ao carregar a página
  document.addEventListener('DOMContentLoaded', function() {
      atualizarContadorCarrinho();
  });
</script>



