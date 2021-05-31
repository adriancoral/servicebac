#!/bin/bash
# Exec artisan commands before start php-fpm
# Use www user
#su www -c  "/usr/local/bin/php /var/www/artisan migrate --force"

apache2-foreground;