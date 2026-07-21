#!/bin/bash
PORT=${PORT:-80}
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-enabled/default

# Cache Laravel configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Seed demo users and master data once for the initial production database.
php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
