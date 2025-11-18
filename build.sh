#!/usr/bin/env bash
set -e

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Installing Node dependencies..."
npm install

echo "Building assets..."
npm run build

echo "Build completed successfully!"
