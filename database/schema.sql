-- Schema para Sistema de Locadora de Carros
-- SQLite Database

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'cliente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de carros
CREATE TABLE IF NOT EXISTS carros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INTEGER NOT NULL,
    cor VARCHAR(50),
    preco_diario DECIMAL(10,2) NOT NULL,
    disponivel BOOLEAN DEFAULT 1,
    imagem VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de aluguéis
CREATE TABLE IF NOT EXISTS alugueis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    carro_id INTEGER NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'ativo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carro_id) REFERENCES carros(id) ON DELETE CASCADE
);

-- Inserir usuário administrador padrão (senha: secret)
INSERT OR IGNORE INTO usuarios (nome, email, senha, role) 
VALUES ('Admin', 'admin@locadora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir carros de exemplo
INSERT OR IGNORE INTO carros (marca, modelo, ano, cor, preco_diario, disponivel) VALUES
('Toyota', 'Corolla', 2022, 'Branco', 120.00, 1),
('Honda', 'Civic', 2023, 'Prata', 140.00, 1),
('Volkswagen', 'Gol', 2021, 'Azul', 90.00, 1),
('Chevrolet', 'Onix', 2023, 'Preto', 110.00, 1),
('Ford', 'Ka', 2022, 'Vermelho', 85.00, 1);