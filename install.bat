@echo off
cd homestead
vagrant box add laravel/homestead
vagrant up
vagrant ssh -c 'cd laravel; composer install; php artisan evidentia:start; php artisan evidentia:createinstance'
