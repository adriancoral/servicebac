#!/bin/bash
mkdir /var/www/storage/
mkdir /var/www/storage/database
touch /var/www/storage/database/service.sqlite
chmod +x /var/www/storage/database/service.sqlite
chmod -R 775 /var/www/storage/database/
chown -R $(whoami) /var/www/storage/database/
php artisan migrate:fresh --force
/usr/bin/supervisord -n -c /etc/supervisord.conf