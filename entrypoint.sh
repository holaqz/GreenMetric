#!/bin/bash
set -e

# Очищаем кэш
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Запускаем миграции (force для production)
php artisan migrate --force

# Запускаем сидеры (создание категорий, индикаторов, пользователей)
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force
php artisan db:seed --force

# Запускаем Apache
exec apache2-foreground
