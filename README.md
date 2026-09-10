# Compra Inteligente (Mais por Menos)

Projeto organizado em quatro camadas independentes:

- `api/`: backend PHP com Slim Framework e integração exclusiva com Gemini;
- `web/`: painel administrativo/dashboard;
- `app/`: aplicativo Ionic, que consome somente a API;
- `database/`: schema e dados de demonstração.

As credenciais de banco e do Gemini ficam em `api/.env`; este arquivo não deve ser versionado.
