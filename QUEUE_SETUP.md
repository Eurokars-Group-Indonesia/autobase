# Queue Setup Guide

## Overview
Aplikasi ini menggunakan Laravel Queue untuk async processing, khususnya untuk logging search history agar tidak mengganggu response time.

## Queue Configuration

### Development Environment

Untuk development, gunakan `sync` driver (langsung dijalankan tanpa queue):

```env
QUEUE_CONNECTION=sync
```

Dengan konfigurasi ini, job akan dijalankan secara synchronous (langsung), tidak perlu menjalankan queue worker.

### Production Environment

Untuk production, gunakan `database` atau `redis` driver untuk async processing.

#### Option 1: Database Queue (Simple)

1. **Update .env**:
```env
QUEUE_CONNECTION=database
```

2. **Migrate jobs table** (jika belum):
```bash
php artisan queue:table
php artisan migrate
```

3. **Run queue worker**:
```bash
php artisan queue:work
```

4. **Run as background service** (Linux):
```bash
# Using supervisor
sudo apt-get install supervisor

# Create supervisor config: /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/service_history/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/service_history/storage/logs/worker.log
stopwaitsecs=3600

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

5. **Run as background service** (Windows):
```bash
# Using NSSM (Non-Sucking Service Manager)
# Download from: https://nssm.cc/download

# Install as service
nssm install LaravelQueue "C:\laragon\bin\php\php-8.x.x\php.exe" "C:\laragon\www\service_history\artisan queue:work --sleep=3 --tries=3"

# Start service
nssm start LaravelQueue

# Check status
nssm status LaravelQueue
```

#### Option 2: Redis Queue (Recommended for Production)

1. **Install Redis**:
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# Windows (using Memurai)
# Download from: https://www.memurai.com/
```

2. **Install PHP Redis extension**:
```bash
# Ubuntu/Debian
sudo apt-get install php-redis

# Windows (Laragon)
# Enable redis extension in php.ini
extension=redis
```

3. **Install Predis package**:
```bash
composer require predis/predis
```

4. **Update .env**:
```env
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

5. **Run queue worker**:
```bash
php artisan queue:work redis
```

## Queue Worker Commands

### Basic Commands

```bash
# Start queue worker
php artisan queue:work

# Start with specific connection
php artisan queue:work redis

# Process only one job
php artisan queue:work --once

# Process jobs for 60 seconds then exit
php artisan queue:work --max-time=60

# Process 100 jobs then exit
php artisan queue:work --max-jobs=100

# Sleep 3 seconds when no jobs available
php artisan queue:work --sleep=3

# Retry failed jobs 3 times
php artisan queue:work --tries=3
```

### Monitoring Commands

```bash
# List all failed jobs
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Delete a failed job
php artisan queue:forget {id}

# Flush all failed jobs
php artisan queue:flush

# Listen to queue (auto-reload on code changes)
php artisan queue:listen
```

### Restart Queue Worker

```bash
# Gracefully restart all queue workers
php artisan queue:restart
```

**Important**: Setelah deploy code baru, selalu restart queue worker!

## Jobs in This Application

### LogSearchHistory Job

- **File**: `app/Jobs/LogSearchHistory.php`
- **Purpose**: Log search history ke database secara async
- **Queue**: default
- **Triggered by**: 
  - Transaction Header search
  - Transaction Body search
- **Parameters**:
  - userId
  - search query
  - date_from
  - date_to
  - execution_time
  - transaction_type (H/B)

## Monitoring Queue

### Check Queue Status

```bash
# Check jobs in queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

### Queue Dashboard (Optional)

Install Laravel Horizon for better queue monitoring:

```bash
composer require laravel/horizon

php artisan horizon:install

php artisan migrate

# Start Horizon
php artisan horizon

# Access dashboard at: http://your-app.test/horizon
```

## Troubleshooting

### Jobs Not Processing

1. **Check queue worker is running**:
```bash
ps aux | grep "queue:work"
```

2. **Check failed jobs**:
```bash
php artisan queue:failed
```

3. **Check logs**:
```bash
tail -f storage/logs/laravel.log
```

### Jobs Failing

1. **View failed job details**:
```bash
php artisan queue:failed
```

2. **Retry failed job**:
```bash
php artisan queue:retry {id}
```

3. **Check error in logs**:
```bash
tail -f storage/logs/laravel.log
```

### Queue Worker Stops

1. **Use supervisor** (Linux) or **NSSM** (Windows) to auto-restart
2. **Monitor with Horizon** for better visibility
3. **Set max-time and max-jobs** to prevent memory leaks:
```bash
php artisan queue:work --max-time=3600 --max-jobs=1000
```

## Best Practices

1. **Always restart queue worker after deploy**:
```bash
php artisan queue:restart
```

2. **Use supervisor/NSSM for production** to ensure queue worker always running

3. **Monitor failed jobs regularly**:
```bash
php artisan queue:failed
```

4. **Set appropriate timeouts**:
```php
// In Job class
public $timeout = 60; // 60 seconds
public $tries = 3; // Retry 3 times
```

5. **Use Redis for better performance** in production

6. **Log errors properly** for debugging:
```php
try {
    // Job logic
} catch (\Exception $e) {
    Log::error('Job failed', ['error' => $e->getMessage()]);
}
```

## Performance Tips

1. **Use multiple workers** for high traffic:
```bash
# Run 3 workers
php artisan queue:work --queue=default --sleep=3 &
php artisan queue:work --queue=default --sleep=3 &
php artisan queue:work --queue=default --sleep=3 &
```

2. **Prioritize queues**:
```bash
php artisan queue:work --queue=high,default,low
```

3. **Use Redis** instead of database for better performance

4. **Monitor memory usage** and set max-time to prevent leaks

5. **Use Horizon** for better monitoring and auto-scaling
