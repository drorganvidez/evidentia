@echo off
git submodule update --init --recursive
cd laradock
COPY .env.example .env
docker-compose up -d nginx mysql phpmyadmin redis workspace
docker exec laradock_workspace rm -f composer.lock
docker exec laradock_workspace composer install
docker exec laradock_workspace php artisan evidentia:start_docker
docker exec laradock_workspace php artisan evidentia:createinstance
docker exec laradock_workspace php artisan key:generate
docker exec laradock_workspace php artisan config:cache

echo
echo WELCOME TO
echo .########.##.....##.####.########..########.##....##.########.####....###...;
echo .##.......##.....##..##..##.....##.##.......###...##....##.....##....##.##..;
echo .##.......##.....##..##..##.....##.##.......####..##....##.....##...##...##.;
echo .######...##.....##..##..##.....##.######...##.##.##....##.....##..##.....##;
echo .##........##...##...##..##.....##.##.......##..####....##.....##..#########;
echo .##.........##.##....##..##.....##.##.......##...###....##.....##..##.....##;
echo .########....###....####.########..########.##....##....##....####.##.....##;
echo

echo Open in a new browser:
echo http://localhost

echo
echo The installation has been completed successfully. Enjoy!
