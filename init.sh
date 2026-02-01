#!/bin/bash

# load .env file
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

# load name from .env file
CONTAINER_NAME=${LARAVEL_CONTAINER_NAME:-"laravel-filament-template"}

echo "Container Name: $CONTAINER_NAME"

### docker dependencies
composer install --ignore-platform-reqs

### build and run docker containers
docker-compose build
docker-compose up -d

## Laravel initialization
docker exec -it $CONTAINER_NAME sh -c "sh init.sh"

### restart container
### php entered FATAL state without vendor files, after a restart it works fine
docker restart $CONTAINER_NAME
