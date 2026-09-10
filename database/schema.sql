-- Schema MySQL do Mais por Menos.
-- As tabelas abaixo sustentam o MVP de listas de compras e comparação de preços.

CREATE TABLE IF NOT EXISTS estabelecimentos_sefaz (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cnpj CHAR(14) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    endereco VARCHAR(500) NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_estabelecimentos_sefaz_cnpj (cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Armazena as contas que criam e administram listas de compras.
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Representa os mercados nos quais os preços dos produtos são coletados.
CREATE TABLE IF NOT EXISTS mercados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj CHAR(14) NULL,
    endereco VARCHAR(500) NULL,
    cidade VARCHAR(120) NOT NULL,
    UNIQUE KEY uq_mercados_cnpj (cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catálogo de produtos que podem ser adicionados às listas e receber preços.
CREATE TABLE IF NOT EXISTS produtos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(32) NULL,
    nome VARCHAR(255) NOT NULL,
    marca VARCHAR(120) NULL,
    categoria VARCHAR(120) NULL,
    peso VARCHAR(80) NULL,
    KEY idx_produtos_codigo_barras (codigo_barras)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lista de compras criada por um usuário.
CREATE TABLE IF NOT EXISTS listas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_listas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    KEY idx_listas_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Item de produto incluído em uma lista, com quantidade e estado de compra.
CREATE TABLE IF NOT EXISTS itens_lista (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lista_id BIGINT UNSIGNED NOT NULL,
    produto_id BIGINT UNSIGNED NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 1.000,
    comprado TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_itens_lista_lista
        FOREIGN KEY (lista_id) REFERENCES listas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_lista_produto
        FOREIGN KEY (produto_id) REFERENCES produtos (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_itens_lista_lista_produto (lista_id, produto_id),
    KEY idx_itens_lista_produto_id (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Histórico de preços coletados para cada produto em cada mercado.
CREATE TABLE IF NOT EXISTS precos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id BIGINT UNSIGNED NOT NULL,
    mercado_id BIGINT UNSIGNED NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_coleta DATETIME NOT NULL,
    origem VARCHAR(100) NOT NULL,
    CONSTRAINT fk_precos_produto
        FOREIGN KEY (produto_id) REFERENCES produtos (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_precos_mercado
        FOREIGN KEY (mercado_id) REFERENCES mercados (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    KEY idx_precos_produto_mercado_data (produto_id, mercado_id, data_coleta),
    KEY idx_precos_mercado_id (mercado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS precos_sefaz (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cnpj_estabelecimento CHAR(14) NOT NULL,
    codigo_barras VARCHAR(14) NULL,
    produto VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    data_coleta DATETIME NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_precos_sefaz_estabelecimento
        FOREIGN KEY (cnpj_estabelecimento) REFERENCES estabelecimentos_sefaz (cnpj),
    KEY idx_precos_sefaz_codigo_barras (codigo_barras),
    KEY idx_precos_sefaz_data_coleta (data_coleta),
    KEY idx_precos_sefaz_estabelecimento_data (cnpj_estabelecimento, data_coleta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
