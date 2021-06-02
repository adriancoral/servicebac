#!/bin/bash
# Exec artisan commands before start php-fpm
# Use www user
#su www -c  "/usr/local/bin/php /var/www/artisan migrate --force"
mkdir storage/database
touch storage/database/service.sqlite
apache2-foreground;