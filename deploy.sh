#!/bin/bash

set -e

echo "🚀 Starting deployment..."

# Copy production env
cp .env.production .env

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate
fi

# Clear and cache configurations
echo "⚙️ Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "✅ Deployment completed!"
