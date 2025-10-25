make install

docker-compose down
docker-compose up -d

docker-compose exec php-fpm php bin/phpunit 

symfony server:start