#!/bin/sh
set -e

VOLUME_NAME="${APP_NAME}-laravel_storage"

docker volume create $VOLUME_NAME

# Verifica se volume está vazio
IS_EMPTY=$(docker run --rm -v $VOLUME_NAME:/data alpine sh -c "[ -z \"\$(ls -A /data)\" ] && echo empty")

if [ "$IS_EMPTY" = "empty" ]; then
  echo "Seeding storage into $VOLUME_NAME..."
  docker run --rm \
    -v $VOLUME_NAME:/dest \
    -v $(pwd)/../../storage:/src:ro \
    alpine sh -c "cp -a /src/. /dest/ && chown -R $USER_ID:$GROUP_ID /dest"
else
  echo "Volume $VOLUME_NAME já contém arquivos, não vou sobrescrever."
fi
