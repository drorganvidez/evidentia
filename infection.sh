cd laradock
docker exec laradock_workspace_1 XDEBUG_MODE=coverage vendor/bin/infection
#docker exec -it laradock_workspace_1 php artisan evidentia:start docker
#docker exec -it laradock_workspace_1 php artisan evidentia:instance