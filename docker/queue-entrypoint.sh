#!/bin/sh

echo "========================================="
echo "Laravel Queue Worker Starting"
echo "========================================="
echo ""

# Wait a bit for app container to finish migrations
echo "Waiting for app container to complete setup..."
sleep 15

echo "Starting queue worker..."
exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --verbose
