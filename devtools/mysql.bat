@echo off
cd ..
call .env
cd laradock
docker exec -it laradock-mysql-1 mysql -u%DB_USERNAME% -p%DB_PASSWORD%
