#!/bin/bash
set -e

# Отладка: проверяем наличие manifest.json
echo "=== Checking manifest.json ==="
ls -la /var/www/html/public/build/
if [ -f /var/www/html/public/build/manifest.json ]; then
    echo "manifest.json found!"
    cat /var/www/html/public/build/manifest.json | head -20
else
    echo "manifest.json NOT found!"
    echo "Contents of public/build:"
    ls -la /var/www/html/public/build/ || true
fi
echo "=============================="

# Запускаем миграции
php artisan migrate --force

# Запускаем Apache
exec apache2-foreground
