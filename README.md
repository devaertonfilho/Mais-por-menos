# 💰 Mais por Menos

**Transformando a ida ao supermercado em uma decisão inteligente.**

O **Mais por Menos** é mais do que um comparador de preços; é uma plataforma de inteligência de consumo desenhada para criar um hábito simples: **escanear cada produto antes de colocá-lo no carrinho**. O objetivo é eliminar a surpresa negativa no caixa e ensinar as famílias a comprarem melhor.

---

## 🎯 Missão do Produto
Ajudar famílias a comprar melhor, gastar menos e tomar decisões inteligentes utilizando a inteligência coletiva da comunidade e dados reais de notas fiscais.

### 🏛️ Os Três Pilares
1. **Planejamento (Antes)**: Listas de compras, orçamentos e sugestões baseadas em histórico.
2. **Compra (Durante)**: Scanner rápido, conferência de preços em tempo real e alertas de orçamento.
3. **Inteligência (Depois)**: Análises pós-compra via IA, estatísticas de economia e recomendações.

---

## 🛠️ Stack Técnica

### Backend (API)
- **Linguagem**: PHP 8.4+
- **Framework**: [Slim Framework](https://slimframework.com/) (Arquitetura REST)
- **Banco de Dados**: MySQL com [PDO](https://www.php.net/manual/pt_BR/class.pdo.php) (Segurança contra SQL Injection)
- **Autenticação**: [Firebase JWT](https://firebase.google.com/docs/auth/admin/verify-id-tokens) (Tokens de acesso seguros)
- **IA**: [Google Gemini API](https://ai.google.dev/) para análises inteligentes.
- **Integração**: API Economiza Alagoas (SEFAZ/AL) para base de preços real.

### Frontend Web (Dashboard)
- **Interface**: HTML5, CSS3, [Bootstrap 5](https://getbootstrap.com/)
- **Interatividade**: jQuery, AJAX
- **Visualização**: [Chart.js](https://www.chartjs.org/) para gráficos de gastos.

### Aplicativo Mobile
- **Framework**: [Ionic Framework](https://ionicframework.com/) + Angular
- **Hardware**: [Capacitor](https://capacitorjs.com/) para integração com a câmera (Scanner de código de barras).

---

## 🚀 Como Executar o Projeto

### 1. Banco de Dados
```bash
# Acesse o MySQL e crie o banco
mysql -u root -p
CREATE DATABASE mais_por_menos;
exit;

# Importe o schema
mysql -u root -p mais_por_menos < database/schema.sql
```

### 2. Backend (API)
```bash
cd api
composer install
cp .env.example .env # Configure as chaves JWT_SECRET, DB_PASSWORD e GEMINI_API_KEY
php -S localhost:8080 -t public
```

### 3. Frontend Web
Abra o arquivo `web/pages/dashboard.html` no seu navegador ou sirva a pasta `web` via servidor HTTP.

### 4. Aplicativo Mobile
```bash
cd app
npm install
ionic serve
```

---

## 🗺️ Roadmap de Desenvolvimento (Modular)

- [x] **Módulo 0 (Admin)**: Integração com API SEFAZ/AL para base inicial de produtos.
- [x] **Módulo 1 (Usuário)**: Cadastro simplificado e autenticação JWT.
- [x] **Módulo 2 (Listas)**: Criação e gestão de listas de compras.
- [ ] **Módulo 3 (Scanner)**: Integração com câmera e confirmação de preços.
- [ ] **Módulo 4 (Acompanhamento)**: Indicadores de gasto em tempo real durante a compra.
- [x] **Módulo 5 (Análise IA)**: Relatórios pós-compra gerados pelo Gemini.
- [ ] **Módulo 6 (Comunidade)**: Compartilhamento colaborativo de preços.
- [ ] **Módulo 7 (Pesquisa Inteligente)**: Comparativo entre preços da SEFAZ vs Comunidade.
- [x] **Módulo 8 (Planejamento)**: Dashboard de resumo e orçamento.
- [ ] **Módulo 9 (Geo)**: Sugestão de mercados próximos com base em economia.

---

## ⚖️ Princípio de Design
> *"Cada nova funcionalidade deve tornar a compra mais simples, mais rápida ou mais econômica. Se aumentar a complexidade sem entregar valor imediato, fica para a próxima versão."*
