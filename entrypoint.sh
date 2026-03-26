#!/bin/bash
set -e

# Перегенерируем ассеты с правильным APP_URL
npm run build

# Запускаем миграции
php artisan migrate --force

# Запускаем Apache
exec apache2-foreground
