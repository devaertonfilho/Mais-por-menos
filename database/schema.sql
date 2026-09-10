-- Schema MySQL do Compra Inteligente.
-- Adicione aqui as tabelas: usuarios, produtos, precos, listas, estoque e mercados.

CREATE TABLE IF NOT EXISTS estabelecimentos_sefaz (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cnpj CHAR(14) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    endereco VARCHAR(500) NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_estabelecimentos_sefaz_cnpj (cnpj)
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
