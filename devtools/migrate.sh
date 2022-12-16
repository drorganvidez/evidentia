#!/bin/bash
cd ..
docker exec -it laradock-workspace-1 php artisan evidentia:migrate
