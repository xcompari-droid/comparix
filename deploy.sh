#!/bin/bash

# Deployment Script pentru Comparix.ro
# Folosește acest script în Forge sau manual pe VPS

set -e

echo "🚀 Starting deployment..."

# Git pull
echo "📦 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📚 Installing dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Install NPM dependencies and build
echo "🎨 Building frontend assets..."
npm ci --production=false
npm run build

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Clear and cache config
echo "⚡ Optimizing..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link
php artisan storage:link

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Clear application cache (optional)
# php artisan cache:clear

echo "✅ Deployment complete!"
