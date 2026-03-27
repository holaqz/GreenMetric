#!/bin/bash
set -e

# Запускаем миграции
php artisan migrate --force

# Запускаем сидеры (создание категорий, индикаторов, пользователей)
php artisan db:seed --class=GreenMetricIndicatorsSeeder
php artisan db:seed

# Запускаем Apache
exec apache2-foreground
