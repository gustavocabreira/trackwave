#!/bin/sh
# bootstrap_macos.sh
# Compatível com macOS (BSD sed, sem GNU getopt)
set -e

echo "Starting (macOS)…"

# --- Checagens básicas ---
if ! command -v docker >/dev/null 2>&1; then
  echo "Erro: Docker não está instalado ou não está no PATH."
  exit 1
fi

# docker compose V2 (subcomando) é o padrão no macOS Desktop
if ! docker compose version >/dev/null 2>&1; then
  echo "Erro: 'docker compose' não está disponível. Atualize o Docker Desktop."
  exit 1
fi

# --- Variáveis de ambiente úteis (opcional) ---
export USER_ID="$(id -u)"
export USER_GROUP="$(id -g)"
export HOST_USER="$(whoami)"

# Default
APP_NAME="laravel"

# --- Parse dos argumentos (sem GNU getopt) ---
while [ $# -gt 0 ]; do
  case "$1" in
    -n|--app-name)
      if [ -n "${2:-}" ]; then
        APP_NAME="$2"
        shift 2
      else
        echo "Erro: falta o valor para $1"
        exit 1
      fi
      ;;
    --) shift; break ;;
    *)  # Ignora args desconhecidos (ou faça 'exit 1' se quiser estrito)
      shift ;;
  esac
done

# --- Preparação do .env ---
if [ ! -f .env ]; then
  cp .env.example .env
fi

# BSD sed (macOS) exige '' após -i
# Substitui a string literal "app_name" pelo nome escolhido
sed -i '' "s|app_name|$APP_NAME|g" .env

# --- Storage/local setup ---
if [ -x "./set_storage.sh" ]; then
  ./set_storage.sh
fi

# --- Sobe containers ---
# docker compose stop  # (opcional) pare antes de subir
docker compose up -d --build

# --- Espera Laravel ficar pronto ---
counter=0
# Aguarda o arquivo /var/www/artisan existir no contêiner "laravel"
# (ajuste o nome do serviço se for diferente no seu compose)
while ! docker compose exec -T laravel test -f /var/www/artisan 2>/dev/null; do
  echo "Waiting for Laravel container to be ready: ${counter}s"
  sleep 5
  counter=$((counter + 5))
done
echo "Laravel container is ready after $counter seconds."

# --- Espera Banco ficar pronto ---
# Ajuste o serviço e a mensagem de log conforme sua imagem (ex.: MySQL/MariaDB)
# Para MySQL oficial, "ready for connections" aparece no log do mysqld
until docker compose logs mysql 2>/dev/null | grep -q "ready for connections"; do
  echo "Waiting Database setup… ${counter}s"
  sleep 5
  counter=$((counter + 5))
done
echo "Database is ready to use after $counter seconds."

# --- Sincroniza .env (host <-> container) ---
docker compose cp .env laravel:/var/www/
docker compose cp laravel:/var/www/.env .env

# --- Instala dependências e prepara app ---
docker compose exec -T laravel composer install
docker compose exec -T laravel npm install
docker compose exec -T laravel php artisan key:generate
docker compose exec -T laravel php artisan migrate
docker compose exec -T laravel npm i chokidar
docker compose exec -T laravel php artisan storage:link

# Reinicia apenas o serviço laravel para aplicar tudo
docker compose restart laravel

echo "Started!"