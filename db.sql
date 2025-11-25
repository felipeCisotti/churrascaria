create database churrascaria;
use churrascaria;

create table usuarios(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
    telefone VARCHAR(20),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    insert into usuarios (nome, email, senha, tipo) values ('adm','adm@adm','adm123','admin');
    
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50),
    imagem VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'confirmado', 'em_preparo', 'a_caminho', 'entregue', 'cancelado') DEFAULT 'pendente',
    endereco_entrega VARCHAR(255),
    pagamento ENUM('dinheiro', 'cartao', 'pix') DEFAULT 'dinheiro',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_reserva DATE NOT NULL,
    horario TIME NOT NULL,
    qtd_pessoas INT NOT NULL,
    status ENUM('pendente', 'confirmada', 'cancelada') DEFAULT 'pendente',
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela para avaliações dos produtos
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL,
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela para faturamento
CREATE TABLE faturamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data DATE NOT NULL,
    total_vendas DECIMAL(10,2) DEFAULT 0,
    total_pedidos INT DEFAULT 0
);

-- Adicionar coluna de avaliação média nos produtos
ALTER TABLE produtos ADD COLUMN avaliacao_media DECIMAL(3,2) DEFAULT 0; 
select * from faturamento;	

select * from usuarios;		
delete from usuarios;
use churrascaria;

CREATE TABLE restaurantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  estado VARCHAR(2) NOT NULL,
  cidade VARCHAR(100) NOT NULL
);

select * from produtos	;

ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL;

-- Tabela para endereços dos usuários
CREATE TABLE enderecos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    titulo VARCHAR(50) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    logradouro VARCHAR(200) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100),
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    principal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
-- Adicionar coluna endereco_entrega_id na tabela pedidos
ALTER TABLE pedidos ADD COLUMN endereco_entrega_id INT NULL;
ALTER TABLE pedidos ADD COLUMN observacoes TEXT NULL AFTER endereco_entrega_id;