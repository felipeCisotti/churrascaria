<?php
include '../includes/connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id'];

$sql = "SELECT tipo FROM usuarios WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $tipo = $usuario['tipo'];

    // SE FOR ADMIN → manda para dashboard
    if ($tipo === 'admin') {
        header("Location: dashboard.php");
        exit;
    }


} else {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';
?>
<a href="carrinho.php" class="btn-carrinho-flutuante">
    🛒
</a>

<div class="corrossel">
<div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="../assets/img/b1.png" class="d-block w-100" alt="...">
    </div>
    <div class="carousel-item">
      <img src="../assets/img/b2.png" class="d-block w-100" alt="...">
    </div>
    <div class="carousel-item">
      <img src="../assets/img/b3.png" class="d-block w-100" alt="...">
    </div>
  </div>
</div>
</div>

<div class="mobile">

  <div class="search-mobile">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" placeholder="Buscar carnes, acompanhamentos...">
    <i class="fa-solid fa-filter"></i>
  </div>

  <div class="categoria-mob">
    <div class="cat-itens">
      <a href="">Todos</a>
      <a href="">Carne</a>
      <a href="">Bebidas</a>
    </div>
  </div>

  <div class="car-mob">
    <div class="caroussel">
      <div class="slides" id="slides-mobile">
        <div class="slide"><img src="../assets/img/c1.png" alt=""></div>
        <div class="slide"><img src="../assets/img/c2.png" alt=""></div>
        <div class="slide"><img src="../assets/img/c3.png" alt=""></div>
        <div class="slide"><img src="../assets/img/c4.png" alt=""></div>
        <div class="slide"><img src="../assets/img/c5.png" alt=""></div>
      </div>
    </div>
    <div class="dots" id="dots-mobile"></div>
  </div>

  <div class="cardapio-menu-mob">
    <h2>Cardápio</h2>

    <div class="slider-categorias">
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/pratos.png" alt=""></a>
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/espetinhos.png" alt=""></a>
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/porcoes.png" alt=""></a>
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/drinks.png" alt=""></a>
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/sobremesas.png" alt=""></a>
      <a class="img-card-mob" href="cardapio.php"><img src="../assets/img/cardapio/rodizio.png" alt=""></a>
    </div>
  </div>

</div>

<div class="desktop">

  <div class="cardapio-menu">
    <h2>Cardápio</h2>

    <div class="cardapio-imgs">
      <a class="img-card" href=""><img src="../assets/img/cardapio/pratos.png" alt=""></a>
      <a class="img-card" href=""><img src="../assets/img/cardapio/espetinhos.png" alt=""></a>
      <a class="img-card" href=""><img src="../assets/img/cardapio/porcoes.png" alt=""></a>
      <a class="img-card" href=""><img src="../assets/img/cardapio/drinks.png" alt=""></a>
      <a class="img-card" href=""><img src="../assets/img/cardapio/sobremesas.png" alt=""></a>
      <a class="img-card" href=""><img src="../assets/img/cardapio/rodizio.png" alt=""></a>
    </div>

    <div class="cardapio-tit">
      <h3>Pratos</h3>
      <h3>Espetinhos</h3>
      <h3>Porções</h3>
      <h3>Drinks</h3>
      <h3>Sobremesas</h3>
      <h3>Rodízio</h3>
    </div>
  </div>


  <div class="car">
<div class="caroussel">
<div class="slides" id="slides">
<div class="slide"><img src="../assets/img/c1.png" alt=""></div>
<div class="slide"><img src="../assets/img/c2.png" alt=""></div>
<div class="slide"><img src="../assets/img/c3.png" alt=""></div>
<div class="slide"><img src="../assets/img/c4.png" alt=""></div>
<div class="slide"><img src="../assets/img/c5.png" alt=""></div>
</div>
<button class="control prev" id="prev">❮</button>
<button class="control next" id="next">❯</button>
</div>
<div class="dots" id="dots"></div>
</div>

<div class="valores-container" id="valores">
  <div class="valores">
    <div class="nume">1</div>
    <div class="texto-valores">
      <h5>Seleção Rigorosa</h5>
      Escolhemos pessoalmente cada peça de carne, avaliando marmoreio, cor e textura.
    </div>
  </div>

  <div class="valores">
    <div class="nume">2</div>
    <div class="texto-valores">
      <h5>Preparo Artesanal</h5>
      As carnes são preparadas com técnicas tradicionais, respeitando o sabor natural.
    </div>
  </div>

  <div class="valores">
    <div class="nume">4</div>
    <div class="texto-valores">
      <h5>Serviço no Ponto</h5>
      Servimos as carnes imediatamente após o preparo, no ponto ideal de cada uma.
    </div>
  </div>

  <div class="valores">
    <div class="nume">3</div>
    <div class="texto-valores">
      <h5>Assamento Perfeito</h5>
      Controlamos temperatura e distância do fogo para cada tipo de corte.
    </div>
  </div>
</div>

<script>
const slides = document.getElementById('slides');
const totalSlides = document.querySelectorAll('.slide').length;
const next = document.getElementById('next');
const prev = document.getElementById('prev');
const dotsContainer = document.getElementById('dots');
let current = 0;
let slidesPerView = 3;
let intervalId = null;

function getSlidesPerView() {
  const w = window.innerWidth;
  if (w <= 480) return 1;
  if (w <= 768) return 2;
  if (w <= 1024) return 2;
  return 3;
}

function buildDots() {
  dotsContainer.innerHTML = '';
  const totalDots = Math.max(1, Math.ceil(totalSlides / slidesPerView));
  for (let i = 0; i < totalDots; i++) {
    const dot = document.createElement('span');
    dot.classList.add('dot');
    if (i === Math.floor(current / slidesPerView)) dot.classList.add('active');
    dot.addEventListener('click', () => moveTo(i));
    dotsContainer.appendChild(dot);
  }
  return document.querySelectorAll('.dot');
}

let dots = [];

function updateDots() {
  dots.forEach(dot => dot.classList.remove('active'));
  const activeIndex = Math.floor(current / slidesPerView);
  if (dots[activeIndex]) dots[activeIndex].classList.add('active');
}

function moveTo(index) {
  current = index * slidesPerView;
  if (current >= totalSlides) current = 0;
  slides.style.transform = `translateX(-${(100 / slidesPerView) * index}%)`;
  updateDots();
}

function nextSlide() {
  current += slidesPerView;
  if (current >= totalSlides) current = 0;
  moveTo(Math.floor(current / slidesPerView));
}

function prevSlide() {
  current -= slidesPerView;
  if (current < 0) current = Math.max(0, totalSlides - slidesPerView);
  moveTo(Math.floor(current / slidesPerView));
}

if (next) next.addEventListener('click', nextSlide);
if (prev) prev.addEventListener('click', prevSlide);

function startAutoPlay() {
  if (intervalId) clearInterval(intervalId);
  intervalId = setInterval(() => {
    nextSlide();
  }, 5000);
}

function setup() {
  slidesPerView = getSlidesPerView();
  dots = buildDots();
  if (current >= totalSlides) current = 0;
  moveTo(Math.floor(current / slidesPerView));
  startAutoPlay();
}

let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    const newSPV = getSlidesPerView();
    if (newSPV !== slidesPerView) {
      slidesPerView = newSPV;
      current = 0;
      dots = buildDots();
      moveTo(0);
      startAutoPlay();
    }
  }, 150);
});

// Suporte a swipe (touch) para mobile
let touchStartX = 0;
let touchEndX = 0;
const touchThreshold = 50;

slides.addEventListener('touchstart', (e) => {
  touchStartX = e.touches[0].clientX;
}, {passive: true});

slides.addEventListener('touchmove', (e) => {
  touchEndX = e.touches[0].clientX;
}, {passive: true});

slides.addEventListener('touchend', () => {
  const diff = touchStartX - touchEndX;
  if (Math.abs(diff) > touchThreshold) {
    if (diff > 0) nextSlide(); else prevSlide();
  }
  touchStartX = 0; touchEndX = 0;
});

setup();
</script>

<?php
include '../includes/footer.php';
?>