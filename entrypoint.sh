#!/bin/bash
set -e

# Очищаем кэш
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Создаём директорию для временных файлов
mkdir -p /var/www/html/storage/app/temp
chmod -R 775 /var/www/html/storage/app/temp

# Запускаем миграции (force для production)
php artisan migrate --force

# Запускаем сидеры (создание категорий, индикаторов, пользователей)
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force
php artisan db:seed --force

# Запускаем Apache
exec apache2-foreground
