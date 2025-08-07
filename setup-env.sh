#!/bin/bash

# Create .env file for Laravel with SQL Server configuration
# This matches the configuration in docker-compose.yml

cat > .env << 'EOF'
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# SQL Server Database Configuration
# These settings match the docker-compose.yml
DB_CONNECTION=sqlsrv
DB_HOST=db
DB_PORT=1433
DB_DATABASE=laravel
DB_USERNAME=sa
DB_PASSWORD=abcDEF123#
DB_TRUST_SERVER_CERTIFICATE=true

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_DATABASE_CONNECTION=null
CACHE_DATABASE_TABLE=cache

MEMCACHED_HOST=127.0.0.1

# Redis Configuration (matching docker-compose.yml)
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (using MailHog from docker-compose.yml)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
EOF

echo "✅ .env file created with SQL Server configuration from docker-compose.yml"

# Generate application key if it doesn't exist
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔐 Generating application key..."
    php artisan key:generate
fi

echo "✅ Environment setup complete!"
echo ""
echo "Database configuration:"
echo "  - Connection: sqlsrv"
echo "  - Host: db (Docker service name)"
echo "  - Port: 1433"
echo "  - Database: laravel"
echo "  - Username: sa"
echo "  - Password: abcDEF123#" 