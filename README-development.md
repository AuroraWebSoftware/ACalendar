
docker-compose up -d
composer test

./vendor/bin/pint
./vendor/bin/phpstan analyse