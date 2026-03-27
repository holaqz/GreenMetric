#!/bin/bash
set -e

# Запускаем миграции (force для production)
php artisan migrate --force

# Запускаем сидеры (создание категорий, индикаторов, пользователей)
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force
php artisan db:seed --force

# Запускаем Apache
exec apache2-foreground
