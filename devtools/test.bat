@echo off
cd ..
docker exec -it laradock-workspace-1 php artisan test
