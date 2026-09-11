#!/bin/zsh
echo "🚀 Iniciando o Servidor API do Mais por Menos..."
echo "🌐 Endereço: http://$(hostname -I | awk '{print $1}'):8080"
echo "⚠️  Certifique-se de que seu celular está no mesmo WiFi!"
echo "------------------------------------------------------------"
cd api && php -S 0.0.0.0:8080 -t public
