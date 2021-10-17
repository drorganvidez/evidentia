@echo off
cd ../homestead
vagrant box remove laravel/homestead --all
vagrant box add laravel/homestead -c
vagrant up
vagrant ssh -c 'cd laravel; composer install; php artisan evidentia:start_homestead; php artisan evidentia:createinstance'
