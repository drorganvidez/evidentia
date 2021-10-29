@echo off
git submodule update --init --recursive
cd homestead
git pull origin master
vagrant box remove laravel/homestead --all
vagrant box add laravel/homestead -c
vagrant up
vagrant ssh -c 'cd evidentia; composer install; php artisan evidentia:start_homestead; php artisan evidentia:createinstance'
