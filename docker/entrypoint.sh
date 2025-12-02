#!/bin/sh
set -e

export COMPOSER_ALLOW_SUPERUSER=1

echo "Fixing permissions for storage and cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing PHP dependencies..."
    composer install --no-dev --prefer-dist --optimize-autoloader
fi

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
echo "Resolving ${DB_HOST}..."
getent hosts "${DB_HOST}" || echo "Could not resolve ${DB_HOST}"

retries=0
until php -r "exit(@fsockopen('${DB_HOST}', (int) ${DB_PORT}) ? 0 : 1);"; do
    retries=$((retries + 1))
    if [ "$retries" -ge 150 ]; then
        echo "Database is not reachable after ${retries} attempts. Exiting."
        exit 1
    fi
    echo "Database not ready yet (attempt ${retries}). Retrying in 2 seconds..."
    sleep 2
done

echo "Creating storage directories..."
mkdir -p /var/www/html/storage/app/public/payment_proofs
mkdir -p /var/www/html/storage/app/public/foods
mkdir -p /var/www/html/storage/app/public/profiles
chown -R www-data:www-data /var/www/html/storage/app/public
chmod -R 777 /var/www/html/storage/app/public

echo "Running migrations and seeders..."
deploy_attempt=0
until php artisan migrate --force && php artisan db:seed --force; do
    deploy_attempt=$((deploy_attempt + 1))
    if [ "$deploy_attempt" -ge 5 ]; then
        echo "Database migrations/seeds failed after ${deploy_attempt} attempts. Exiting."
        exit 1
    fi
    echo "Database not ready yet. Retrying in 5 seconds..."
    sleep 5
done

rm -f /var/www/html/public/storage
php artisan storage:link --force

(
    if [ ! -d "node_modules" ] || [ ! -f "node_modules/.bin/vite" ]; then
        echo "Installing Node dependencies..."
        npm install
    fi
    echo "Building assets..."
    npm run build
) &

echo "Optimizing application..."
php artisan optimize

exec "$@"
