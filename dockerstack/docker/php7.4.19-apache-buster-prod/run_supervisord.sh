#!/bin/bash
mkdir /var/www/storage/
mkdir /var/www/storage/database
touch /var/www/storage/database/service.sqlite
chmod +x /var/www/storage/database/service.sqlite
php artisan migrate:fresh --force
/usr/bin/supervisord -n -c /etc/supervisord.conf