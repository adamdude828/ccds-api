#!/bin/bash
set -e

# Create necessary directories
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Ensure PHP-FPM config exists
if [ ! -f /usr/local/php/etc/php-fpm.conf ]; then
    if [ -f /usr/local/php/etc/php-fpm.conf.default ]; then
        echo "PHP-FPM config not found, creating from default template"
        cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf
        # Update the default config to match our needs
        sed -i 's/;daemonize = yes/daemonize = no/g' /usr/local/php/etc/php-fpm.conf
        sed -i 's/;error_log = log\/php-fpm.log/error_log = \/proc\/self\/fd\/2/g' /usr/local/php/etc/php-fpm.conf
        # Configure www pool
        if [ -d /usr/local/php/etc/php-fpm.d ]; then
            echo "[www]
user = www-data
group = www-data
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
access.log = /proc/self/fd/2
clear_env = no
catch_workers_output = yes
decorate_workers_output = no" > /usr/local/php/etc/php-fpm.d/www.conf
        fi
    else
        echo "WARNING: No PHP-FPM configuration found and no default template available"
    fi
fi

# Update .env file if it exists
if [ -f /var/www/html/.env ]; then
    echo "Updating .env file with environment variables..."
    # Update database connection settings
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=${DB_CONNECTION:-mysql}/" /var/www/html/.env
    sed -i "s/DB_HOST=.*/DB_HOST=${DB_HOST:-db}/" /var/www/html/.env
    sed -i "s/DB_PORT=.*/DB_PORT=${DB_PORT:-3306}/" /var/www/html/.env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-laravel}/" /var/www/html/.env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-root}/" /var/www/html/.env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-password}/" /var/www/html/.env
    
    # Update APP_KEY if provided
    if [ ! -z "$APP_KEY" ]; then
        sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
    fi
    
    # If APP_KEY is not set or empty, generate a new one
    if grep -q "APP_KEY=" /var/www/html/.env && ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
        echo "Generating new APP_KEY..."
        cd /var/www/html
        php artisan key:generate --force
    fi
    
    echo "Environment variables updated in .env file"
fi

# Wait for database to be ready if WAIT_FOR_DB is set
if [ "${WAIT_FOR_DB}" = "true" ]; then
    echo "Waiting for database connection..."
    if [ "${DB_CONNECTION}" = "sqlsrv" ]; then
        echo "Waiting for SQL Server to be ready..."
        for i in {1..30}; do
            if command -v sqlcmd >/dev/null 2>&1 && sqlcmd -S ${DB_HOST} -U ${DB_USERNAME} -P "${DB_PASSWORD}" -Q "SELECT 1" &> /dev/null; then
                echo "SQL Server is ready"
                break
            fi
            echo "Waiting for SQL Server to be ready (attempt $i/30)..."
            sleep 2
        done
    elif [ "${DB_CONNECTION}" = "mysql" ]; then
        echo "Waiting for MySQL/MariaDB to be ready..."
        for i in {1..30}; do
            if mysqladmin ping -h "${DB_HOST}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" --silent &> /dev/null; then
                echo "MySQL/MariaDB is ready"
                break
            fi
            echo "MySQL is unavailable - sleeping"
            sleep 2
        done
    else
        echo "Unknown database connection type: ${DB_CONNECTION}"
    fi
fi

# Run Laravel migrations if RUN_MIGRATIONS is set
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    cd /var/www/html
    if [ -f artisan ]; then
        php artisan migrate --force
        echo "Migrations completed"
        
        # Optimize Laravel
        echo "Optimizing Laravel..."
        php artisan config:clear
        php artisan config:cache
        php artisan route:clear
        php artisan route:cache
        php artisan view:clear
        php artisan view:cache
        echo "Laravel has been set up successfully!"
    else
        echo "WARNING: Laravel artisan file not found. Skipping migrations."
    fi
fi

echo "Starting supervisord..."
# Execute the command passed to the script
exec "$@" 
