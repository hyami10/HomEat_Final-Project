#!/bin/bash
set -e  # Exit on error

echo "========================================="
echo "Stopping HomeAt Application"
echo "========================================="
echo ""

# Parse arguments
CLEAN_ALL=0
CLEAN_DATA=0
CLEAN_CACHE=0

for arg in "$@"; do
    case $arg in
        --all)
            CLEAN_ALL=1
            ;;
        --data)
            CLEAN_DATA=1
            ;;
        --cache)
            CLEAN_CACHE=1
            ;;
        --help|-h)
            echo "Usage: ./stop.sh [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  (no args)    Stop containers only (preserve data & cache)"
            echo "  --all        Stop + clean everything (containers, volumes, cache, built images)"
            echo "  --data       Stop + clean database volumes (remove all data)"
            echo "  --cache      Stop + clean cache files (Laravel cache, logs, compiled views)"
            echo "  --help       Show this help message"
            echo ""
            echo "Examples:"
            echo "  ./stop.sh                Stop containers"
            echo "  ./stop.sh --all          Nuclear option - clean everything!"
            echo "  ./stop.sh --data         Reset database"
            echo "  ./stop.sh --cache        Clear Laravel cache"
            exit 0
            ;;
        *)
            echo "Unknown option: $arg"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# 1. Stop containers
echo "🛑 Stopping Docker containers..."
docker-compose down

if [ "$CLEAN_ALL" == "1" ] || [ "$CLEAN_DATA" == "1" ]; then
    echo ""
    echo "🗑️  Removing Docker volumes (database will be deleted)..."
    docker-compose down -v
    
    # Also remove named volumes explicitly
    docker volume rm homeat_mysql_data 2>/dev/null || true
    docker volume rm homeat_nginx_cache 2>/dev/null || true
    
    echo "✅ Database volumes removed"
fi

if [ "$CLEAN_ALL" == "1" ]; then
    echo ""
    echo "🗑️  Removing Docker images..."
    docker rmi homeat-app 2>/dev/null || true
    docker rmi homeat-nginx 2>/dev/null || true
    
    echo "✅ Docker images removed"
fi

# 2. Clean Laravel cache
if [ "$CLEAN_ALL" == "1" ] || [ "$CLEAN_CACHE" == "1" ]; then
    echo ""
    echo "🧹 Cleaning Laravel cache files..."
    
    # Remove storage cache
    rm -rf storage/framework/cache/data/* 2>/dev/null || true
    rm -rf storage/framework/sessions/* 2>/dev/null || true
    rm -rf storage/framework/views/* 2>/dev/null || true
    
    # Keep .gitkeep files
    touch storage/framework/cache/data/.gitkeep 2>/dev/null || true
    touch storage/framework/sessions/.gitkeep 2>/dev/null || true
    touch storage/framework/views/.gitkeep 2>/dev/null || true
    
    # Remove logs
    rm -rf storage/logs/*.log 2>/dev/null || true
    touch storage/logs/.gitkeep 2>/dev/null || true
    
    # Remove bootstrap cache
    rm -f bootstrap/cache/config.php 2>/dev/null || true
    rm -f bootstrap/cache/routes*.php 2>/dev/null || true
    rm -f bootstrap/cache/services.php 2>/dev/null || true
    rm -f bootstrap/cache/packages.php 2>/dev/null || true
    
    echo "✅ Laravel cache cleaned"
fi

# 3. Clean uploaded files (only if --all)
if [ "$CLEAN_ALL" == "1" ]; then
    echo ""
    echo "🗑️  Cleaning uploaded files..."
    
    # Remove uploaded files but keep directories
    rm -rf storage/app/public/foods/* 2>/dev/null || true
    rm -rf storage/app/public/profiles/* 2>/dev/null || true
    rm -rf storage/app/public/payment_proofs/* 2>/dev/null || true
    
    # Keep .gitignore
    touch storage/app/public/.gitignore 2>/dev/null || true
    
    echo "✅ Uploaded files cleaned"
fi

# 4. Clean build artifacts (only if --all)
if [ "$CLEAN_ALL" == "1" ]; then
    echo ""
    echo "🗑️  Cleaning build artifacts..."
    
    # Remove compiled assets
    rm -rf public/build/* 2>/dev/null || true
    rm -rf public/hot 2>/dev/null || true
    
    # Remove node_modules (optional - uncomment if you want)
    # rm -rf node_modules 2>/dev/null || true
    
    # Remove vendor (optional - uncomment if you want)
    # rm -rf vendor 2>/dev/null || true
    
    echo "✅ Build artifacts cleaned"
fi

# 5. Remove orphaned Docker resources
if [ "$CLEAN_ALL" == "1" ]; then
    echo ""
    echo "🧹 Cleaning orphaned Docker resources..."
    
    # Remove orphaned networks
    docker network prune -f 2>/dev/null || true
    
    # Remove dangling images
    docker image prune -f 2>/dev/null || true
    
    echo "✅ Docker cleanup completed"
fi

# 6. Summary
echo ""
echo "========================================="
echo "✅ Cleanup completed!"
echo "========================================="
echo ""

if [ "$CLEAN_ALL" == "1" ]; then
    echo "🔥 NUCLEAR CLEANUP - Everything removed:"
    echo "   ✅ Containers stopped"
    echo "   ✅ Database volumes deleted"
    echo "   ✅ Docker images removed"
    echo "   ✅ Laravel cache cleared"
    echo "   ✅ Uploaded files deleted"
    echo "   ✅ Build artifacts removed"
    echo "   ✅ Docker orphans cleaned"
    echo ""
    echo "⚠️  You'll need to run 'bash start.sh' to rebuild everything"
elif [ "$CLEAN_DATA" == "1" ]; then
    echo "📊 Data cleanup:"
    echo "   ✅ Containers stopped"
    echo "   ✅ Database volumes deleted"
    echo ""
    echo "⚠️  All data lost! Run 'bash start.sh' to start fresh"
elif [ "$CLEAN_CACHE" == "1" ]; then
    echo "🧹 Cache cleanup:"
    echo "   ✅ Containers stopped"
    echo "   ✅ Laravel cache cleared"
    echo ""
    echo "ℹ️  Run 'bash start.sh --preserve' to keep data"
else
    echo "🛑 Containers stopped (data preserved)"
    echo ""
    echo "ℹ️  To start again: bash start.sh --preserve"
    echo "ℹ️  To clean data: ./stop.sh --data"
    echo "ℹ️  To clean everything: ./stop.sh --all"
fi

echo "========================================="
