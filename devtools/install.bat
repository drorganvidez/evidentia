@echo off
cd ..
git submodule update --init --recursive

rem load environment variables
copy .env.dev .env
call .env

rem changes environment
copy .env.laradock laradock\.env
sed -i "" "s/{{MYSQL_DATABASE}}/%DB_DATABASE%/g" laradock\.env
sed -i "" "s/{{MYSQL_PASSWORD}}/%DB_PASSWORD%/g" laradock\.env
sed -i "" "s/{{MYSQL_PORT}}/%DB_PORT%/g" laradock\.env
sed -i "" "s/{{MYSQL_ROOT_PASSWORD}}/%DB_PASSWORD%/g" laradock\.env

rem changes SQL entry point database
copy entrypoint.sql start.sql
sed -i "" "s/database_user/%DB_USERNAME%/g" start.sql
sed -i "" "s/database_name/%DB_DATABASE%/g" start.sql
sed -i "" "s/database_password/%DB_PASSWORD%/g" start.sql
copy start.sql laradock\mysql\docker-entrypoint-initdb.d\createdb.sql
del start.sql

rem up and run containers
cd laradock
docker compose down
rd /s /q "%userprofile%\.laradock\data\mysql"
rd /s /q "%userprofile%\.laradock\data\redis"
docker compose up -d --build nginx mysql phpmyadmin redis workspace
docker exec laradock-php-fpm-1 chown -R www-data:www-data /var/www/storage
docker exec laradock-workspace-1 del composer.lock
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
