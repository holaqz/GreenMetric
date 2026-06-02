#!/bin/bash
set -e

# 🔐 Права на запись
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
mkdir -p /var/www/html/storage/app/temp
chmod -R 775 /var/www/html/storage/app/temp

# 🗄️ Миграции и сидеры
php artisan migrate --force
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force --no-interaction 2>/dev/null || true
php artisan db:seed --force --no-interaction 2>/dev/null || true

# 🌐 НАСТРОЙКА APACHE (исправленная часть)
if [ -n "$PORT" ]; then
    echo "🔌 Configuring Apache to listen on port $PORT..."
    
    # 1. Копируем наш правильный apache.conf
    if [ -f "/var/www/html/apache.conf" ]; then
        echo "📄 Using custom apache.conf..."
        cp /var/www/html/apache.conf /etc/apache2/sites-available/000-default.conf
    fi
    
    # 2. Устанавливаем ServerName (убирает предупреждение)
    grep -q "ServerName" /etc/apache2/apache2.conf || echo "ServerName localhost" >> /etc/apache2/apache2.conf
    
    # 3. Меняем порт в конфиге (ПРАВИЛЬНАЯ замена)
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf
    
    # 4. Включаем mod_rewrite для Laravel
    a2enmod rewrite 2>/dev/null || true
fi

# 🪵 Логи в stderr
export LOG_CHANNEL=stderr

echo "🚀 Starting Apache on port ${PORT:-80}..."
exec apache2-foreground