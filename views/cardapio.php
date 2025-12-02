<?php
include '../includes/header.php';
include '../includes/connect.php';


$sqlCategorias = "SELECT DISTINCT categoria 
                  FROM produtos 
                  WHERE ativo = 1 
                  ORDER BY categoria";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_COLUMN);
?>

<link rel="stylesheet" href="../css/cardapio.css">

<body class="cardapio-page">
    <div class="subheader">
        <img src="../assets/img/subheader.png" alt="">
    </div>
    <a href="carrinho.php" class="btn-carrinho-flutuante">
    <i class="fa fa-shopping-cart"></i> 
</a>

    <?php foreach ($categorias as $categoria): ?>
        <?php
        $catID = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($categoria));
        ?>

        <div class="cardapio-content">
            <h2><?php echo strtoupper(htmlspecialchars($categoria)); ?></h2>

            <div class="car">
                <div class="carousel">
                    <div class="slides" id="slides-<?php echo $catID; ?>">

                        <?php
                        $sqlProdutos = "SELECT * FROM produtos 
                                        WHERE categoria = :categoria 
                                        AND ativo = 1
                                        ORDER BY nome ASC";
                        $stmtProdutos = $pdo->prepare($sqlProdutos);
                        $stmtProdutos->execute(['categoria' => $categoria]);
                        $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (count($produtos) > 0): ?>
                            <?php foreach ($produtos as $produto): ?>
                                <div class="slide">
                                    <div class="produto-card" data-produto-id="<?php echo $produto['id']; ?>">
                                        <img src="../assets/img/cardapio/<?php echo htmlspecialchars($produto['imagem']); ?>"
                                            alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                        <p class="produto-descricao"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                                        <div class="card-bottom">
                                            <p class="produto-preco">R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                            </p>
                                            <i class="fa-solid fa-cart-shopping add-carrinho"
                                                data-produto-id="<?php echo $produto['id']; ?>"
                                                data-produto-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                data-produto-preco="<?php echo $produto['preco']; ?>"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="slide">
                                <p>Nenhum produto nesta categoria.</p>
                            </div>
                        <?php endif; ?>

                    </div>

                    

                </div>

                <button class="btn-prev" id="prev-<?php echo $catID; ?>">
    <i class="fa-solid fa-chevron-left"></i>
</button>

<button class="btn-next" id="next-<?php echo $catID; ?>">
    <i class="fa-solid fa-chevron-right"></i>
</button>


                <div class="dots" id="dots-<?php echo $catID; ?>"></div>
            </div>
        </div>

        <div class="separador">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
            <img src="../assets/img/separacao.png" alt="">
        </div>
    <?php endforeach; ?>

    <script src="https://kit.fontawesome.com/03a8c367de.js" crossorigin="anonymous"></script>

    <script>

        // Inicializar todos carrosséis
        document.addEventListener('DOMContentLoaded', function () {
            <?php foreach ($categorias as $categoria):
                $catID = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($categoria)); ?>
                initCarousel('<?php echo $catID; ?>');
            <?php endforeach; ?>
        });

        function initCarousel(categoria) {
            const slides = document.getElementById('slides-' + categoria);
            if (!slides) return;

            const totalSlides = slides.querySelectorAll('.slide').length;
            const next = document.getElementById('next-' + categoria);
            const prev = document.getElementById('prev-' + categoria);
            const dotsContainer = document.getElementById('dots-' + categoria);

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
                return dotsContainer.querySelectorAll('.dot');
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
                }, 3000);
            }

            function setup() {
                slidesPerView = getSlidesPerView();
                dots = buildDots();
                // adjust current if it exceeds bounds
                if (current >= totalSlides) current = 0;
                moveTo(Math.floor(current / slidesPerView));
                startAutoPlay();
            }

            // debounce resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    const newSPV = getSlidesPerView();
                    if (newSPV !== slidesPerView) {
                        slidesPerView = newSPV;
                        // rebuild dots and reset position to avoid partial slides
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
            const touchThreshold = 50; // pixels

            slides.addEventListener('touchstart', (e) => {
                touchStartX = e.touches[0].clientX;
            }, {passive: true});

            slides.addEventListener('touchmove', (e) => {
                touchEndX = e.touches[0].clientX;
            }, {passive: true});

            slides.addEventListener('touchend', () => {
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > touchThreshold) {
                    if (diff > 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }
                touchStartX = 0;
                touchEndX = 0;
            });

            setup();
        }

        // Funções de carrinho

        document.querySelectorAll('.add-carrinho').forEach(icon => {
            icon.addEventListener('click', function (e) {
                e.preventDefault();

                const produtoId = this.getAttribute('data-produto-id');
                const produtoNome = this.getAttribute('data-produto-nome');

                adicionarAoCarrinho(produtoId, 1, produtoNome);
            });
        });

        function adicionarAoCarrinho(produtoId, quantidade, produtoNome) {
            fetch('../includes/verificar_login.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logado) {
                        if (confirm('Você precisa estar logado para adicionar itens ao carrinho. Deseja fazer login?')) {
                            window.location.href = 'login.php';
                        }
                        return;
                    }

                    fetch('../includes/adicionar_carrinho.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `produto_id=${produtoId}&quantidade=${quantidade}`
                    })
                        .then(response => response.text())
                        .then(result => {
                            if (result === 'success') {
                                mostrarMensagemSucesso(produtoNome);
                                atualizarContadorCarrinho();
                            } else {
                                mostrarMensagemErro();
                            }
                        })
                        .catch(() => mostrarMensagemErro());
                })
                .catch(() => mostrarMensagemErro());
        }

        function mostrarMensagemSucesso(produtoNome) {
            const toastExistente = document.querySelector('.toast-carrinho');
            if (toastExistente) toastExistente.remove();

            const toast = document.createElement('div');
            toast.className = 'toast-carrinho';
            toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <div class="toast-text">
                    <strong>${produtoNome}</strong>
                    <span>adicionado ao carrinho!</span>
                </div>
                <a href="carrinho.php" class="toast-link">Ver Carrinho</a>
                <button class="toast-close">&times;</button>
            </div>
        `;

            document.body.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 100);

            toast.querySelector('.toast-close').addEventListener('click', () => fecharToast(toast));

            setTimeout(() => fecharToast(toast), 4000);
        }

        function fecharToast(toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }

        function mostrarMensagemErro() {
            alert('Erro ao adicionar produto ao carrinho. Tente novamente.');
        }

        function atualizarContadorCarrinho() {
            const contador = document.querySelector('.carrinho-count');
            if (contador) {
                let countAtual = parseInt(contador.textContent) || 0;
                contador.textContent = countAtual + 1;
                contador.style.display = 'flex';
            }
        }

        document.querySelectorAll('.add-carrinho').forEach(icon => {
            icon.addEventListener('click', function () {
                this.style.transform = 'scale(1.2)';
                this.style.color = 'var(--vermelho)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                    this.style.color = '';
                }, 300);
            });
        });

    </script>
</body>

<?php
include '../includes/footer.php';
?>