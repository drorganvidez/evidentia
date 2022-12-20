@echo off
cd ..
call .env.dev.bat
cd laradock
docker exec -it laradock-mysql-1 mysql -u%DB_USERNAME% -p%DB_PASSWORD%
cd ..
cd devtools
