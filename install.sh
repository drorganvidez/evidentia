#!/bin/bash
sudo /bin/bash -c 'echo -e "192.168.10.10 evidentia.test" >> /etc/hosts'
cd homestead
vagrant box add laravel/homestead
vagrant up
vagrant ssh -c 'cd laravel; composer install; php artisan evidentia:start; php artisan evidentia:createinstance'
