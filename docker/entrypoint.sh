#!/bin/sh

echo "========================================="
echo "Laravel Docker Entrypoint"
echo "========================================="
echo ""

# Check if we should skip database operations
if [ "$SKIP_DB_SETUP" = "true" ]; then
    echo "SKIP_DB_SETUP=true, skipping database operations..."
    echo "You can run migrations manually with:"
    echo "  docker-compose exec app php artisan migrate --force"
    echo ""
    exec "$@"
fi

# Wait for database to be ready
echo "Waiting for database connection..."
echo "Database: $DB_HOST:$DB_PORT/$DB_DATABASE"
max_attempts=15
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if php artisan db:show > /dev/null 2>&1; then
        echo "✓ Database connection successful!"
        break
    fi
    
    attempt=$((attempt + 1))
    if [ $attempt -lt $max_attempts ]; then
        echo "Attempt $attempt/$max_attempts - Waiting for database..."
        sleep 3
    fi
done

if [ $attempt -eq $max_attempts ]; then
    echo ""
    echo "⚠ Warning: Could not connect to database after $max_attempts attempts"
    echo ""
    echo "Please check:"
    echo "  - DB_HOST=$DB_HOST"
    echo "  - DB_PORT=$DB_PORT"
    echo "  - DB_DATABASE=$DB_DATABASE"
    echo "  - DB_USERNAME=$DB_USERNAME"
    echo "  - MySQL server is running and accessible"
    echo ""
    echo "You can:"
    echo "  1. Run migrations manually: docker-compose exec app php artisan migrate --force"
    echo "  2. Set SKIP_DB_SETUP=true to skip this check"
    echo ""
    echo "Continuing without database setup..."
else
    echo ""
    echo "Running database migrations..."
    if php artisan migrate --force; then
        echo "✓ Migrations completed successfully!"
    else
        echo "⚠ Warning: Migrations failed!"
        echo "You can run manually: docker-compose exec app php artisan migrate --force"
    fi
    
    # Run seeders if SEED_DATABASE is set to true
    if [ "$SEED_DATABASE" = "true" ]; then
        echo ""
        echo "Running database seeders..."
        if php artisan db:seed --force; then
            echo "✓ Seeders completed successfully!"
        else
            echo "⚠ Warning: Seeders failed or no seeders available!"
        fi
    fi
    
    echo ""
    echo "Creating storage link..."
    php artisan storage:link 2>/dev/null || echo "Storage link already exists"
    
    echo ""
    echo "Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo ""
echo "========================================="
echo "Laravel is ready!"
echo "========================================="
echo ""

# Execute the main command (supervisord or queue worker)
exec "$@"
