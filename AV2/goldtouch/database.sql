-- Gold Touch - Estética e Beleza
-- Banco de dados MySQL

CREATE DATABASE IF NOT EXISTS goldtouch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goldtouch;

-- Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha_hash VARCHAR(255) NOT NULL,
    pontos INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Serviços
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao_minutos INT NOT NULL DEFAULT 60,
    categoria ENUM('cabelo','manicure','maquiagem','massagem','sobrancelha') NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- Planos mensais
CREATE TABLE planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    servicos_inclusos TEXT,
    ativo TINYINT(1) DEFAULT 1
);

-- Agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    status ENUM('pendente','confirmado','concluido','cancelado') DEFAULT 'pendente',
    valor_pago DECIMAL(10,2),
    forma_pagamento ENUM('credito','debito','pix','dinheiro'),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id)
);

-- Avaliações
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    cliente_id INT NOT NULL,
    nota ENUM('satisfeito','pouco_satisfeito','insatisfeito') NOT NULL,
    comentario TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Cupons
CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    desconto_percent INT,
    desconto_valor DECIMAL(10,2),
    pontos_necessarios INT DEFAULT 0,
    validade DATE,
    ativo TINYINT(1) DEFAULT 1
);

-- Cupons resgatados
CREATE TABLE cupons_resgatados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    cupom_id INT NOT NULL,
    usado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (cupom_id) REFERENCES cupons(id)
);

-- Dados iniciais - Serviços
INSERT INTO servicos (nome, descricao, preco, duracao_minutos, categoria) VALUES
('Corte feminino', 'Corte personalizado com lavagem e finalização', 80.00, 60, 'cabelo'),
('Coloração completa', 'Coloração com tintura profissional e hidratação', 250.00, 180, 'cabelo'),
('Manicure simples', 'Esmaltação com cutilagem e tratamento', 50.00, 45, 'manicure'),
('Pedicure', 'Pedicure completa com esfoliação', 70.00, 60, 'manicure'),
('Maquiagem social', 'Maquiagem completa para eventos', 150.00, 90, 'maquiagem'),
('Design de sobrancelha', 'Modelagem e design personalizado', 80.00, 30, 'sobrancelha'),
('Massagem relaxante', 'Massagem corporal com óleos essenciais', 120.00, 60, 'massagem'),
('Progressiva', 'Alisamento progressivo com produtos premium', 300.00, 240, 'cabelo');

-- Dados iniciais - Planos
INSERT INTO planos (nome, descricao, preco, servicos_inclusos) VALUES
('Plano Prata', '4 serviços básicos por mês', 180.00, 'Manicure + Pedicure + Sobrancelha + Corte'),
('Plano Ouro', '6 serviços premium por mês', 350.00, 'Todos do Prata + Coloração + Maquiagem'),
('Plano Diamante', 'Serviços ilimitados + prioridade no agendamento', 600.00, 'Todos os serviços sem limite');

-- Dados iniciais - Cupons
INSERT INTO cupons (codigo, desconto_percent, pontos_necessarios, validade) VALUES
('TARDE20', 20, 0, DATE_ADD(NOW(), INTERVAL 30 DAY)),
('PRIMEIRAVISITA', 15, 0, DATE_ADD(NOW(), INTERVAL 60 DAY)),
('VIP50', 50, 500, DATE_ADD(NOW(), INTERVAL 90 DAY));
