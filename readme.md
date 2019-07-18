
## Instalacion 
Ejecutar los siguiente comandos:

```sh
cp .env.example .env  
composer install   
yarn or npm Install  
php artisan key:generate  
php artisan jwt:secret  
```

### Despliegue con docker
Ejecutar los siguiente comandos:

```sh
git submodule update --init --recursive
cp -f docs/docker/docker-compose.yml docs/docker/env-example laradock/
cd laradock
docker-compose up -d nginx php-fpm
```