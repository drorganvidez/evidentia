@echo off
cd ..
git submodule update --init --recursive
COPY .env.laradock laradock\.env
COPY createdb.sql laradock\mysql\docker-entrypoint-initdb.d\createdb.sql
cd laradock
docker-compose up -d nginx mysql phpmyadmin redis workspace
docker exec laradock-php-fpm-1 chown -R www-data:www-data /var/www/storage
docker exec laradock-workspace-1 rm -f composer.lock
docker exec laradock-workspace-1 composer install
docker exec laradock-workspace-1 npm install
docker exec laradock-workspace-1 npm run build
docker exec laradock-workspace-1 php artisan evidentia:start
docker exec laradock-workspace-1 php artisan evidentia:instance
docker exec laradock-workspace-1 php artisan key:generate

echo
echo WELCOME TO
echo .########.##.....##.####.########..########.##....##.########.####....###...;
echo .##.......##.....##..##..##.....##.##.......###...##....##.....##....##.##..;
echo .##.......##.....##..##..##.....##.##.......####..##....##.....##...##...##.;
echo .######...##.....##..##..##.....##.######...##.##.##....##.....##..##.....##;
echo .##........##...##...##..##.....##.##.......##..####....##.....##..#########;
echo .##.........##.##....##..##.....##.##.......##...###....##.....##..##.....##;
echo .########....###....####.########..########.##....##....##....####.##.....##;
echo

echo Open in a new browser:
echo http://localhost

echo
echo The installation has been completed successfully. Enjoy!

pause
