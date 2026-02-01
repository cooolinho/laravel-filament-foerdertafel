# laravel-filament-template

## 1. clone repository
```bash
git clone git@github.com:cooolinho/laravel-filament-template.git
```

## 2. Installation
### create .env file for docker-compose.yaml
```bash
cp .env.example .env
```
### set your own LARAVEL_CONTAINER_NAME in .env file
```bash
LARAVEL_CONTAINER_NAME=laravel-filament-template
```

### run init script
```bash
sh init.sh
```

## 3. Open Admin Dashboard
http://localhost/admin/login
```
E-Mmail:  admin@example.com
Password: secret
```

## References
- [Filament 4](https://filamentadmin.com/)
- [Laravel 12](https://laravel.com/)
- [Docker](https://www.docker.com/)
- [Docker-Compose](https://docs.docker.com/compose/)
- [MySQL](https://hub.docker.com/r/mysql/mysql-server)
- [Redis](https://hub.docker.com/_/redis)
- [meilisearch](https://hub.docker.com/r/getmeili/meilisearch)
- [mailpit](https://hub.docker.com/r/axllent/mailpit)
- [selenium](https://hub.docker.com/r/selenium/standalone-chromium)
