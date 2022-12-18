cd laradock
docker-compose up -d nginx
echo "nginx cargado"
docker-compose up -d mysql
echo "mysql cargado"
docker-compose up -d phpmyadmin
echo "phpmyadmin cargado"
docker-compose up -d redis
echo "redis cargado"
docker-compose up -d workspace
echo "workspace cargado"
echo "Mirar a ver si hay un fallo en la traza de ejecución, si no, ya está todo desplegado correctamente"
