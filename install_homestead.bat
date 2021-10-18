@echo off
rm -r .gitmodules
rm -r homestead
git submodule add -f https://github.com/drorganvidez/homestead.git homestead
cd homestead
vagrant box remove laravel/homestead --all
vagrant box add laravel/homestead -c
vagrant up
vagrant ssh -c 'cd evidentia; composer install; php artisan evidentia:start_homestead; php artisan evidentia:createinstance'
