FROM php:8.4-apache

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

# Копируем .env.production для production сборки
COPY .env.production .env

# Копируем composer.json и composer.lock сначала для кэширования
COPY composer.json composer.lock ./

# Устанавливаем PHP зависимости без скриптов
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Копируем остальные файлы проекта (игнорируя .gitignore)
COPY --chown=www-data:www-data . .

# Игнорируем .gitignore при копировании
RUN rm -rf .gitignore

# Устанавливаем Node зависимости и билдим ассеты с ENV переменными
ENV ASSET_URL=https://greenmetric.onrender.com
RUN npm install
RUN npm run build

# Настраиваем права доступа только для необходимых директорий
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/build \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public/build

# Копируем Apache конфиг
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Копируем шаблон для экспорта
COPY --chown=www-data:www-data storage/app/templates/master_template.docx /var/www/html/storage/app/templates/master_template.docx

# Копируем entrypoint скрипт
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Открываем порт
EXPOSE 80

# Запускаем Apache через entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
