@echo off

rem we move to root folder
cd ..

rem download laradock repository
git submodule update --init --recursive

rem set Laravel development environment
copy .env.dev .env >nul

rem set Laradock environment
copy .env.laradock laradock\.env >nul
copy docker-compose.stub laradock\docker-compose.yml >nul

rem load environment variables (only for Windows platforms)
call .env.dev.bat

echo Setting development environment variables for Laravel and Laradock, please wait...

rem Llama a la función y proporciona los parámetros necesarios
call :setinfile {{MYSQL_DATABASE}} %DB_DATABASE% laradock\.env
call :setinfile {{MYSQL_PORT}} %DB_PORT% laradock\.env
call :setinfile {{MYSQL_PASSWORD}} %DB_PASSWORD% laradock\.env
call :setinfile {{MYSQL_ROOT_PASSWORD}} %DB_PASSWORD% laradock\.env

rem changes SQL entry point database
copy entrypoint.sql start.sql >nul

call :setinfile database_user %DB_USERNAME% start.sql
call :setinfile database_name %DB_DATABASE% start.sql
call :setinfile database_password %DB_PASSWORD% start.sql

copy start.sql laradock\mysql\docker-entrypoint-initdb.d\createdb.sql >nul
del start.sql

rem up and run containers
cd laradock
docker compose down
rd /s /q "%userprofile%\.laradock\data" 2>&1
docker compose up -d --build nginx mysql phpmyadmin redis workspace
docker exec laradock-php-fpm-1 chown -R www-data:www-data /var/www/storage
docker exec laradock-workspace-1 del composer.lock
docker exec laradock-workspace-1 composer install
docker exec laradock-workspace-1 npm install
docker exec laradock-workspace-1 npm run build
docker exec laradock-workspace-1 php artisan evidentia:start
docker exec laradock-workspace-1 php artisan evidentia:instance
docker exec laradock-workspace-1 php artisan key:generate

echo.
echo WELCOME TO
echo .########.##.....##.####.########..########.##....##.########.####....###...;
echo .##.......##.....##..##..##.....##.##.......###...##....##.....##....##.##..;
echo .##.......##.....##..##..##.....##.##.......####..##....##.....##...##...##.;
echo .######...##.....##..##..##.....##.######...##.##.##....##.....##..##.....##;
echo .##........##...##...##..##.....##.##.......##..####....##.....##..#########;
echo .##.........##.##....##..##.....##.##.......##...###....##.....##..##.....##;
echo .########....###....####.########..########.##....##....##....####.##.....##;
echo.

echo Open in a new browser:
echo http://localhost

echo.
echo The installation has been completed successfully. Enjoy!

cd ..
cd devtools

pause

rem Define la función y sus parámetros
:setinfile
setlocal enabledelayedexpansion
set "search=%~1"
set "replace=%~2"
set "textFile=%~3"

rem Recorre cada línea del archivo especificado
for /f "delims=" %%i in (%textFile%) do (
    set "line=%%i"
    set "line=!line:%search%=%replace%!"
    echo !line!>>temp.txt
)

rem Reemplaza el archivo original con la versión modificada
move /Y temp.txt %textFile% >nul
endlocal
goto :EOF


