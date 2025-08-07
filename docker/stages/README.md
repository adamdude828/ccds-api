# Staged Docker Build for Laravel with PHP 8.4.5 and SQL Server

This directory contains a staged Docker build process for a Laravel application with PHP 8.4.5 and SQL Server support. The build is divided into three stages to make it easier to debug and maintain.

## Stages

### Stage 1: PHP Base
- Builds PHP 8.4.5 from source on Alpine Linux
- Includes all necessary PHP extensions for Laravel
- Creates a minimal PHP environment

### Stage 2: SQL Server Extensions
- Builds on top of the PHP Base image
- Installs Microsoft ODBC Driver and SQL Server tools
- Compiles and installs SQL Server extensions (sqlsrv and pdo_sqlsrv) from source
- Creates a wrapper script to ensure extensions are loaded correctly

### Stage 3: Laravel Application (Multi-stage build)
- **Builder Stage**: 
  - Builds on top of the PHP with SQL Server image
  - Installs Nginx and Supervisor
  - Configures the environment for Laravel
  - Copies the Laravel application code
- **Production Stage**:
  - Starts from a clean Alpine image
  - Installs only runtime dependencies
  - Copies compiled PHP, extensions, and configuration from the builder stage
  - Creates a smaller, optimized production image

## Building the Images

Use the `build-all.sh` script to build the Docker images:

```bash
# Build all stages
./build-all.sh

# Build only up to a specific stage
./build-all.sh --stage=1  # Only build PHP Base
./build-all.sh --stage=2  # Build PHP Base and SQL Server Extensions
```

## Testing the Images

After building, you can test each stage:

```bash
# Test Stage 1: PHP Base
docker run --rm php-base:latest
docker run --rm php-base:latest php /usr/local/bin/test-php.php

# Test Stage 2: SQL Server Extensions
docker run --rm php-sqlsrv:latest
```

### Testing with Docker Compose

For a more complete testing environment, you can use the provided `docker-compose.yml` file:

```bash
# Start the application with SQL Server
docker-compose up -d

# Check the logs
docker-compose logs -f

# Stop the containers
docker-compose down
```

This will start both the Laravel application and a SQL Server container, with all the necessary environment variables configured.

## Production Deployment

For production deployment, run the Laravel application with SQL Server:

```bash
# Start the SQL Server container
docker run -d --name sqlserver \
  -e 'ACCEPT_EULA=Y' \
  -e 'SA_PASSWORD=YourStrong!Passw0rd' \
  -e 'MSSQL_PID=Developer' \
  -p 1433:1433 \
  mcr.microsoft.com/mssql/server:2022-latest

# Start the Laravel application container
docker run -d --name laravel_app \
  -p 80:80 \
  --link sqlserver:db \
  -e 'DB_CONNECTION=sqlsrv' \
  -e 'DB_HOST=db' \
  -e 'DB_PORT=1433' \
  -e 'DB_DATABASE=reviews' \
  -e 'DB_USERNAME=sa' \
  -e 'DB_PASSWORD=YourStrong!Passw0rd' \
  -e 'WAIT_FOR_DB=true' \
  -e 'RUN_MIGRATIONS=true' \
  -e 'APP_ENV=production' \
  -e 'LOG_CHANNEL=stderr' \
  -e 'CACHE_DRIVER=file' \
  laravel-app-php845-sqlsrv:latest
```

## Notes

- This build process is designed for production use
- The multi-stage build approach creates a smaller, optimized production image
- For local development, use Herd or your preferred local development environment
- The SQL Server extensions are compiled from source to ensure compatibility with PHP 8.4.5
- A wrapper script is used to ensure the SQL Server extensions are loaded correctly
- The platform warning during build is expected when building on ARM64 machines 