#!/bin/bash
cd homestead
vagrant box add laravel/homestead
vagrant up
sudo /bin/bash -c 'echo -e "192.168.10.10 evidentia.test" >> /etc/hosts'
vagrant ssh -c 'cd laravel; composer install; php artisan evidentia:start; php artisan evidentia:createinstance'
