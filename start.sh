#!/bin/bash
set -e  # Exit on error

echo "========================================="
echo "Starting HomeAt Application"
echo "========================================="

# Parse arguments
PRESERVE_DATA=0
if [ "$1" == "--preserve" ]; then
    PRESERVE_DATA=1
    echo "Mode: PRESERVE DATA (incremental migration)"
else
    echo "Mode: FRESH START (clean database)"
    echo "Tip: Use './start.sh --preserve' to keep existing data"
fi
echo ""

# 1. Ensure .env exists
if [ ! -f .env ]; then
    echo "📄 Creating .env file from example..."
    cp .env.example .env
    echo "⚠️  IMPORTANT: Please edit .env and set:"
    echo "   - ADMIN_PASSWORD"
    echo "   - USER_DEFAULT_PASSWORD"
    echo ""
fi

# Check if required passwords are set
if ! grep -q "^ADMIN_PASSWORD=.\+" .env 2>/dev/null || ! grep -q "^USER_DEFAULT_PASSWORD=.\+" .env 2>/dev/null; then
    echo "❌ ERROR: ADMIN_PASSWORD and USER_DEFAULT_PASSWORD must be set in .env"
    echo ""
    echo "Please edit .env and set strong passwords:"
    echo "   ADMIN_PASSWORD=YourSecurePassword123!"
    echo "   USER_DEFAULT_PASSWORD=YourSecurePassword123!"
    echo ""
    exit 1
fi

# 2. Stop and Clean Previous Containers
echo "🛑 Stopping old containers..."
docker-compose down

if [ "$PRESERVE_DATA" == "0" ]; then
    echo "🗑️  Removing old volumes (fresh start)..."
    docker-compose down -v
fi

# Force remove specific containers just in case
docker rm -f hom-eat-db hom-eat-app hom-eat-nginx 2>/dev/null || true

# 3. Build and Start Containers
echo ""
echo "🚀 Building and starting Docker containers..."
docker-compose up -d --build

# 4. Wait for Database to be Ready
echo ""
echo "⏳ Waiting for database to be ready..."
retries=0
until docker-compose exec -T app php artisan db:show --json 2>/dev/null | grep -q "mysql"; do
    retries=$((retries + 1))
    if [ "$retries" -ge 30 ]; then
        echo "❌ Database failed to start after 30 attempts."
        exit 1
    fi
    echo "   Database not ready yet (attempt $retries/30). Waiting 2 seconds..."
    sleep 2
done
echo "✅ Database is ready!"

# 5. Application Setup
echo ""
echo "⚙️  Setting up application..."

# Generate App Key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

# 6. Generate SSL Certificates if HTTPS is enabled
echo ""
if grep -q "^APP_FORCE_HTTPS=true" .env 2>/dev/null; then
    echo "🔒 HTTPS is enabled (APP_FORCE_HTTPS=true)"
    
    if [ ! -f "ssl/cert.pem" ] || [ ! -f "ssl/key.pem" ]; then
        echo "📜 SSL certificates not found. Generating self-signed certificates..."
        
        # Create ssl directory if not exists
        mkdir -p ssl
        
        # Generate self-signed certificate (valid for 365 days)
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout ssl/key.pem \
            -out ssl/cert.pem \
            -subj "/C=ID/ST=Jakarta/L=Jakarta/O=HomEat Development/CN=localhost" \
            -addext "subjectAltName=DNS:localhost,DNS:*.localhost,IP:127.0.0.1" \
            2>/dev/null
        
        # Set proper permissions
        chmod 644 ssl/cert.pem 2>/dev/null || true
        chmod 600 ssl/key.pem 2>/dev/null || true
        
        echo "✅ SSL certificates generated successfully!"
        echo "   - Certificate: ssl/cert.pem"
        echo "   - Private Key: ssl/key.pem"
    else
        echo "✅ SSL certificates already exist"
        
        # Check expiration (optional warning)
        cert_expiry=$(openssl x509 -enddate -noout -in ssl/cert.pem 2>/dev/null | cut -d= -f2)
        if [ -n "$cert_expiry" ]; then
            echo "   Certificate expires: $cert_expiry"
        fi
    fi
else
    echo "ℹ️  HTTPS is disabled (APP_FORCE_HTTPS=false or not set)"
    echo "   To enable HTTPS, set APP_FORCE_HTTPS=true in .env"
fi

# 7. Database Migration & Seeding (Handled by entrypoint.sh)
# echo ""
# if [ "$PRESERVE_DATA" == "1" ]; then
#     echo "📊 Running incremental migrations (preserving data)..."
#     docker-compose exec -T app php artisan migrate --force
#     echo "⚠️  Skipping seeder (use --preserve only if you know what you're doing)"
# else
#     echo "📊 Running fresh migrations with seeders..."
#     docker-compose exec -T app php artisan migrate:fresh --seed --force
# fi

# 7. Create storage symlink
echo ""
echo "🔗 Creating storage symlink..."
docker-compose exec -T app php artisan storage:link --force

# 8. Clear caches
echo ""
echo "🧹 Clearing application caches..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan view:clear

# 9. Success Message
echo ""
echo "========================================="
echo "✅ Application is ready!"
echo "========================================="
echo ""

# Show URLs based on HTTPS setting
if grep -q "^APP_FORCE_HTTPS=true" .env 2>/dev/null; then
    echo "🌐 URLs:"
    echo "   HTTP:  http://localhost:8000"
    echo "   HTTPS: https://localhost:8443 (self-signed cert)"
else
    echo "🌐 URL: http://localhost:8000"
fi

echo ""
echo "👤 Default Accounts:"
echo "   Admin:"
echo "     Email: admin@homeat.com"
echo "     Password: (check your .env ADMIN_PASSWORD)"
echo ""
echo "   User:"
echo "     Email: user@homeat.com"
echo "     Password: (check your .env USER_DEFAULT_PASSWORD)"
echo ""
echo "📝 Logs: docker-compose logs -f"
echo "🛑 Stop: ./stop.sh"
echo "========================================="