@echo off
cd homestead
vagrant box add laravel/homestead
vagrant up
echo 192.168.10.10 evidentia.test >> %WINDIR%\System32\Drivers\Etc\Hosts
vagrant ssh -c 'cd laravel; composer install; php artisan evidentia:start; php artisan evidentia:createinstance'
