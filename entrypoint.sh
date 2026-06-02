#!/bin/bash
set -e

# 🔥 Важно: не очищаем config:cache, если он не использовался в deploy
# Просто убеждаемся, что права на запись есть
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Создаём директорию для временных файлов
mkdir -p /var/www/html/storage/app/temp
chmod -R 775 /var/www/html/storage/app/temp

# Запускаем миграции (force для production)
# ⚠️ Если миграции уже прошли в deploy.sh, это безопасно (idempotent)
php artisan migrate --force

# Запускаем сидеры (создание категорий, индикаторов, пользователей)
# Используем --no-interaction для CI/CD
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force --no-interaction 2>/dev/null || true
php artisan db:seed --force --no-interaction 2>/dev/null || true

# 🌐 Настраиваем Apache на порт из переменной $PORT (Render)
if [ -n "$PORT" ]; then
    echo "🔌 Configuring Apache to listen on port $PORT..."
    # Создаём временный конфиг для Apache
    cat > /etc/apache2/ports.conf <<EOF
Listen $PORT
<IfModule ssl_module>
    Listen 443
</IfModule>
<IfModule mod_gnutls.c>
    Listen 443
</IfModule>
EOF
    
    # Обновляем VirtualHost
    sed -i "s/:80/>:$PORT/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
fi

# 🪵 Логи в stderr для отображения в Render Dashboard
export LOG_CHANNEL=stderr

# Запускаем Apache
echo "🚀 Starting Apache..."
exec apache2-foreground 