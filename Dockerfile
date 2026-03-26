FROM php:8.2-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm \
    unzip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Включаем Apache mod_rewrite
RUN a2enmod rewrite

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . .

# Устанавливаем PHP зависимости
RUN composer install --no-dev --optimize-autoloader

# Устанавливаем Node зависимости и билдим ассеты
RUN npm install && npm run build

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Копируем Apache конфиг
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Открываем порт
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
