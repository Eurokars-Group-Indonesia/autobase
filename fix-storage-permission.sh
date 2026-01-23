#!/bin/bash

echo "========================================="
echo "Fix Laravel Storage Permissions"
echo "========================================="
echo ""

# Get container name
CONTAINER_NAME="laravel_app"

echo "Fixing permissions for storage directory..."
echo ""

# Fix permissions inside container
docker exec -it $CONTAINER_NAME sh -c "
    echo 'Setting ownership to www-data:www-data...'
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    
    echo 'Setting directory permissions to 775...'
    find /var/www/html/storage -type d -exec chmod 775 {} \;
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;
    
    echo 'Setting file permissions to 664...'
    find /var/www/html/storage -type f -exec chmod 664 {} \;
    find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;
    
    echo ''
    echo 'Permissions fixed successfully!'
    echo ''
    echo 'Current storage permissions:'
    ls -la /var/www/html/storage
"

echo ""
echo "========================================="
echo "Done!"
echo "========================================="
echo ""
echo "If you still have permission issues, try:"
echo "  docker exec -it laravel_app chmod -R 777 /var/www/html/storage"
echo ""
