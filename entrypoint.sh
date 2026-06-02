#!/bin/bash
set -e

# 🔐 ПРАВА НА ЗАПИСЬ - Устанавливаем ДО запуска Apache
echo "🔐 Setting storage permissions..."

# Создаём ВСЕ необходимые директории
mkdir -p /var/www/html/storage/app/temp
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/bootstrap/cache

# Выдаем права НА ВСЁ storage и bootstrap/cache
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Меняем владельца на www-data (важно для Docker/Render)
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true

# 🗄️ Миграции и сидеры
php artisan migrate --force
php artisan db:seed --class=GreenMetricIndicatorsSeeder --force --no-interaction 2>/dev/null || true
php artisan db:seed --class=CycleSeeder --force --no-interaction 2>/dev/null || true
php artisan db:seed --class=DatabaseSeeder --force --no-interaction 2>/dev/null || true

# 🌐 Настройка Apache на порт из переменной $PORT (Render)
if [ -n "$PORT" ]; then
    echo "🔌 Configuring Apache to listen on port $PORT..."
    
    # Копируем наш правильный apache.conf
    if [ -f "/var/www/html/apache.conf" ]; then
        echo "📄 Using custom apache.conf..."
        cp /var/www/html/apache.conf /etc/apache2/sites-available/000-default.conf
    fi
    
    # Устанавливаем ServerName (убирает предупреждение)
    grep -q "ServerName" /etc/apache2/apache2.conf || echo "ServerName localhost" >> /etc/apache2/apache2.conf
    
    # Меняем порт в конфиге
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf
    
    # Включаем mod_rewrite для Laravel
    a2enmod rewrite 2>/dev/null || true
fi

# 🪵 Логи в stderr
export LOG_CHANNEL=stderr

echo "🚀 Starting Apache on port ${PORT:-80}..."
exec apache2-foreground