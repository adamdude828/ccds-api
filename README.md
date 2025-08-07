# Docker Setup for Laravel with SQL Server

This project uses a multi-stage Docker build process to create optimized images for both production and development environments.

## Directory Structure

```
docker/
├── README.md                       # Overall explanation of the Docker setup
├── build-all.sh                    # Main build script
├── database/                       # SQL Server database setup
│   ├── Dockerfile                  # Database image definition
│   └── data/                       # Persisted database data
└── stages/                         # Multi-stage build process
    ├── 1-php-base/                 # Stage 1: PHP 8.3.19 base
    │   └── Dockerfile              # Builds PHP from source
    ├── 2-sqlsrv/                   # Stage 2: SQL Server extensions
    │   └── Dockerfile              # Adds SQL Server support to PHP
    ├── 3-laravel/                  # Stage 3: Laravel application (production)
    │   ├── Dockerfile              # Adds Laravel with Nginx and Supervisor
    │   ├── config/                 # Configuration files for stage 3
    │   │   ├── nginx.conf          # Nginx configuration
    │   │   ├── php-fpm.conf        # PHP-FPM configuration
    │   │   ├── php.ini             # PHP configuration
    │   │   └── supervisord.conf    # Supervisor configuration
    │   └── scripts/                # Scripts for stage 3
    │       └── entrypoint.sh       # Container entrypoint script
    └── 4-xdebug/                   # Stage 4: Development environment
        └── Dockerfile              # Adds Xdebug for debugging
```

## Why Multi-Stage Builds?

We've split the Docker build into multiple stages for several reasons:

1. **Build Time Efficiency**: Building everything in a single Dockerfile would result in very long build times. By using stages, we can build incrementally, reducing rebuild time when making changes.

2. **Layer Caching**: Multi-stage builds allow Docker to cache intermediate layers, speeding up subsequent builds.

3. **Separation of Concerns**: Each stage focuses on a specific aspect of the environment (PHP, SQL Server extensions, Laravel, debugging), making maintenance easier.

4. **Optimization**: The production image only includes what's needed for running the application, resulting in a smaller final image.

## Build Stages

The build process consists of 4 stages:

### Stage 1: PHP Base
- Builds PHP 8.3.19 from source with required extensions
- Creates the foundation for all subsequent stages
- Tagged as `php-base:latest`

### Stage 2: SQL Server Extensions
- Adds Microsoft SQL Server extensions to PHP
- Installs the ODBC drivers and sqlsrv/pdo_sqlsrv PHP extensions
- Tagged as `php-sqlsrv:latest`

### Stage 3: Laravel Application (Production)
- Adds Nginx, Supervisor, and other dependencies for running Laravel
- Creates a full production-ready image
- Uses a multi-stage approach internally to optimize the image size
- Tagged as `laravel-app-php8319-sqlsrv:latest`
- Used in production environments

### Stage 4: Xdebug (Development)
- Builds upon Stage 3 to add Xdebug for debugging
- Configures Xdebug to connect back to the host IDE
- Tagged as `laravel-app-php8319-sqlsrv-xdebug:latest`
- Used in development environments

## Docker Compose Files

The project includes two Docker Compose files:

1. **docker-compose.yml**: For local development
   - Uses the Stage 4 image with Xdebug
   - Mounts the project directory as a volume for live code changes
   - Suitable for active development

2. **docker-compose-production.yml**: For production
   - Uses the Stage 3 image without Xdebug
   - Does not mount code volumes (runs from the code baked into the image)
   - Optimized for production deployment

## Building the Images

To build all stages, run the build script:

```bash
./docker/stages/build-all.sh
```

To build only up to a specific stage (1, 2, 3, or 4):

```bash
./docker/stages/build-all.sh --stage=<stage_number>
```

## Running the Application

For development:
```bash
docker-compose up -d
```

For production:
```bash
docker-compose -f docker-compose-production.yml up -d
```

## Database Migrations

In both environments, the entrypoint script will automatically run migrations if the `RUN_MIGRATIONS` environment variable is set to `true` (configured in the docker-compose files). 