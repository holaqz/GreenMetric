#!/bin/bash
set -e

# Запускаем миграции
php artisan migrate --force

# Запускаем Apache
exec apache2-foreground
