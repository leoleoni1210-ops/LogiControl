
CREATE DATABASE IF NOT EXISTS logiControl
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE logiControl;

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    peso DECIMAL(10,2),
    quantidade INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de endereços
CREATE TABLE IF NOT EXISTS enderecos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificacao VARCHAR(150) NOT NULL,
    cep VARCHAR(15),
    logradouro VARCHAR(200) NOT NULL,
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de transportadores
CREATE TABLE IF NOT EXISTS transportadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    documento VARCHAR(30) NOT NULL,
    telefone VARCHAR(30),
    email VARCHAR(150),
    placa_veiculo VARCHAR(10),
    modelo_veiculo VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de fretes
CREATE TABLE IF NOT EXISTS fretes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    data_frete DATE NOT NULL,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    transportador_id INT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pendente',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_frete_origem
        FOREIGN KEY (origem_id) REFERENCES enderecos(id),

    CONSTRAINT fk_frete_destino
        FOREIGN KEY (destino_id) REFERENCES enderecos(id),

    CONSTRAINT fk_frete_transportador
        FOREIGN KEY (transportador_id) REFERENCES transportadores(id)
) ENGINE=InnoDB;

-- Tabela de relacionamento entre fretes e produtos
CREATE TABLE IF NOT EXISTS frete_produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    frete_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,

    CONSTRAINT fk_frete_produtos_frete
        FOREIGN KEY (frete_id) REFERENCES fretes(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_frete_produtos_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;
