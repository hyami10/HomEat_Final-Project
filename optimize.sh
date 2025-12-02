#!/bin/bash

# Performance optimization script for Laravel
# Run this after deployment to cache configs, routes, and views

echo "🚀 Optimizing Laravel application for production..."

# Check if running locally without dependencies
if [ ! -f "vendor/autoload.php" ]; then
    if [ -f "docker-compose.yml" ] && command -v docker >/dev/null; then
        echo "⚠️  Dependencies not found locally. Attempting to run inside Docker..."
        docker compose exec app bash optimize.sh
        exit $?
    fi
fi

# Cache configuration
echo "📦 Caching configuration..."
php artisan config:cache

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache

# Cache views
echo "👁️  Caching views..."
php artisan view:cache

# Cache events
echo "📅 Caching events..."
php artisan event:cache

echo "✅ Optimization complete!"
echo ""
echo "ℹ️  To clear all caches, run: php artisan optimize:clear"
