#!/bin/bash
cd ..

source .env

# changes environment
cp .env.laradock laradock/.env
sed -i '' 's/{{MYSQL_PASSWORD}}/'"$DB_PASSWORD"'/g' laradock/.env
sed -i '' 's/{{MYSQL_PORT}}/'"$DB_PORT"'/g' laradock/.env
sed -i '' 's/{{MYSQL_ROOT_PASSWORD}}/'"$DB_PASSWORD"'/g' laradock/.env

# changes SQL entry point database
cp entrypoint.sql start.sql
sed -i '' 's/database_name/'"$DB_DATABASE"'/g' start.sql
sed -i '' 's/database_password/'"$DB_PASSWORD"'/g' start.sql

cp start.sql laradock/mysql/docker-entrypoint-initdb.d/createdb.sql
rm start.sql