<?php
include '../includes/header.php';
include '../includes/connect.php';

// Pega as categorias existentes com produtos ativos
$sqlCategorias = "SELECT DISTINCT categoria 
                  FROM produtos 
                  WHERE ativo = 1 
                  ORDER BY categoria";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_COLUMN);
?>

<body class="cardapio-page">
    <div class="subheader">
        <img src="../assets/img/subheader.png" alt="">
    </div>

    <?php foreach ($categorias as $categoria): ?>
        <div class="cardapio-content">
            <h2><?php echo strtoupper(htmlspecialchars($categoria)); ?></h2>
            
            <div class="car">
                <div class="carousel">
                    <div class="slides" id="slides-<?php echo htmlspecialchars($categoria); ?>">
                        <?php
                        // Pega os produtos dessa categoria
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
                                            <p class="produto-preco">R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
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
                    
                    <button class="control prev" id="prev-<?php echo htmlspecialchars($categoria); ?>">❮</button>
                    <button class="control next" id="next-<?php echo htmlspecialchars($categoria); ?>">❯</button>
                </div>
                <div class="dots" id="dots-<?php echo htmlspecialchars($categoria); ?>"></div>
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
    // Inicializar carrosséis para cada categoria
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($categorias as $categoria): ?>
            initCarousel('<?php echo htmlspecialchars($categoria); ?>');
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
        const slidesPerView = 3;

        // Criar os dots dinamicamente
        const totalDots = Math.ceil(totalSlides / slidesPerView);
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => moveTo(i));
            dotsContainer.appendChild(dot);
        }

        const dots = dotsContainer.querySelectorAll('.dot');

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

        if (next) {
            next.addEventListener('click', () => {
                current += slidesPerView;
                if (current >= totalSlides) current = 0;
                const index = Math.floor(current / slidesPerView);
                moveTo(index);
            });
        }

        if (prev) {
            prev.addEventListener('click', () => {
                current -= slidesPerView;
                if (current < 0) current = totalSlides - slidesPerView;
                const index = Math.floor(current / slidesPerView);
                moveTo(index);
            });
        }

        // Auto-play
        setInterval(() => {
            current += slidesPerView;
            if (current >= totalSlides) current = 0;
            const index = Math.floor(current / slidesPerView);
            moveTo(index);
        }, 5000);

        // Inicializar
        updateDots();
    }

    // Adicionar produto ao carrinho
    document.querySelectorAll('.add-carrinho').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            
            const produtoId = this.getAttribute('data-produto-id');
            const produtoNome = this.getAttribute('data-produto-nome');
            
            adicionarAoCarrinho(produtoId, 1, produtoNome);
        });
    });

    function adicionarAoCarrinho(produtoId, quantidade, produtoNome) {
        // Verificar se usuário está logado
        fetch('../includes/verificar_login.php')
            .then(response => response.json())
            .then(data => {
                if (!data.logado) {
                    if (confirm('Você precisa estar logado para adicionar itens ao carrinho. Deseja fazer login?')) {
                        window.location.href = 'login.php';
                    }
                    return;
                }

                // Usuário está logado, adicionar ao carrinho
                fetch('../includes/adicionar_carrinho.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
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
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensagemErro();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensagemErro();
            });
    }

    function mostrarMensagemSucesso(produtoNome) {
        // Remover toast existente
        const toastExistente = document.querySelector('.toast-carrinho');
        if (toastExistente) {
            toastExistente.remove();
        }

        // Criar novo toast
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
        
        // Animação de entrada
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Fechar toast ao clicar no X
        toast.querySelector('.toast-close').addEventListener('click', function() {
            fecharToast(toast);
        });
        
        // Fechar toast automaticamente após 4 segundos
        setTimeout(() => {
            fecharToast(toast);
        }, 4000);
    }

    function fecharToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    function mostrarMensagemErro() {
        alert('Erro ao adicionar produto ao carrinho. Tente novamente.');
    }

    function atualizarContadorCarrinho() {
        const contador = document.querySelector('.carrinho-count');
        if (contador) {
            const countAtual = parseInt(contador.textContent) || 0;
            contador.textContent = countAtual + 1;
            contador.style.display = 'flex';
        }
    }

    // Adicionar efeito visual ao clicar no ícone
    document.querySelectorAll('.add-carrinho').forEach(icon => {
        icon.addEventListener('click', function() {
            this.style.transform = 'scale(1.2)';
            this.style.color = 'var(--vermelho)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
                this.style.color = '';
            }, 300);
        });
    });
    </script>

    <style>

:root{
    --marrom: #802B01;
    --escuro: #421700;
    --claro: #B85F1B;
    --vermelho: #BD1600;
    --amarelo: #DC7700;
}


.car {
        margin-bottom: 5px;
    }

    .carousel {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .slides {
        display: flex;
        transition: transform 0.5s ease;
        gap: 20px;
    }

    .slide {
        flex: 0 0 calc(33.333% - 14px); /* 3 slides por view */
        min-width: 0;
    }

    .produto-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .produto-card:hover {
        transform: translateY(-5px);
    }

    .produto-card img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .produto-card h3 {
        color: var(--vermelho);
        font-size: 1.2rem;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .produto-descricao {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .card-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .produto-preco {
        color: var(--vermelho);
        font-weight: bold;
        font-size: 1.1rem;
        margin: 0;
    }

    .add-carrinho {
        color: var(--vermelho);
        font-size: 1.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 8px;
        border-radius: 50%;
    }

    .add-carrinho:hover {
        background: #f8f9fa;
    }

    .control {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: var(--vermelho);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .control:hover {
        background: var(--escuro);
        transform: translateY(-50%) scale(1.1);
    }

    .prev {
        left: 10px;
    }

    .next {
        right: 10px;
    }

    .dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dot.active {
        background: var(--amarelo);
        transform: scale(1.2);
    }

    .dot:hover {
        background: var(--claro);
    }

    /* Estilos para o toast */
    .toast-carrinho {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 10000;
        border-left: 4px solid var(--vermelho);
        max-width: 350px;
    }

    .toast-carrinho.show {
        transform: translateX(0);
    }

    .toast-content {
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toast-content i {
        color: var(--vermelho);
        font-size: 1.2rem;
    }

    .toast-text {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .toast-text strong {
        color: #333;
        font-size: 0.9rem;
    }

    .toast-text span {
        color: #666;
        font-size: 0.8rem;
    }

    .toast-link {
        background: var(--vermelho);
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .toast-link:hover {
        opacity: 0.9;
    }

    .toast-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toast-close:hover {
        color: #333;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .slide {
            flex: 0 0 calc(50% - 10px); /* 2 slides por view em tablets */
        }
        
        .control {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .slide {
            flex: 0 0 calc(100% - 10px); /* 1 slide por view em mobile */
        }
        
        .control {
            display: none; /* Esconder botões em mobile muito pequeno */
        }
        
        .produto-card img {
            height: 150px;
        }
    }
    </style>
</body>

<?php
include '../includes/footer.php';
?>