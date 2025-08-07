#!/bin/bash
set -e

# Function to display usage
usage() {
    echo "Usage: $0 [options]"
    echo "Options:"
    echo "  --stage=N       Build up to stage N (1, 2, 3, or 4)"
    echo "  --no-cache      Build without using Docker cache"
    echo "  --help          Display this help message"
    echo ""
    echo "Stages:"
    echo "  1: PHP Base - Builds PHP 8.3.19 from source"
    echo "  2: SQL Server - Adds SQL Server extensions"
    echo "  3: Laravel - Adds Laravel application with Nginx and Supervisor (multi-stage build)"
    echo "  4: Xdebug - Adds Xdebug support for development"
    exit 1
}

# Parse command line arguments
STAGE=4
NO_CACHE=""

for arg in "$@"; do
    case $arg in
        --stage=*)
            STAGE="${arg#*=}"
            ;;
        --no-cache)
            NO_CACHE="--no-cache"
            ;;
        --help)
            usage
            ;;
        *)
            echo "Unknown option: $arg"
            usage
            ;;
    esac
done

# Validate stage
if [[ ! "$STAGE" =~ ^[1-4]$ ]]; then
    echo "Error: Invalid stage number. Must be 1, 2, 3, or 4."
    usage
fi

# Get the project root directory
PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
STAGES_DIR="$PROJECT_ROOT/docker/stages"

echo "Building up to stage $STAGE..."
if [ -n "$NO_CACHE" ]; then
    echo "Building with no cache..."
fi

# Stage 1: PHP Base
echo "Building Stage 1: PHP Base..."
cd "$STAGES_DIR/1-php-base"
docker build $NO_CACHE --platform=linux/amd64 -t php-base:latest .

if [ "$STAGE" -eq 1 ]; then
    echo "Stage 1 complete. Run 'docker run --rm php-base:latest' to test."
    echo "Or run 'docker run --rm php-base:latest php /usr/local/bin/test-php.php' to see PHP info."
    exit 0
fi

# Stage 2: SQL Server
echo "Building Stage 2: SQL Server Extensions..."
cd "$STAGES_DIR/2-sqlsrv"
docker build $NO_CACHE --platform=linux/amd64 -t php-sqlsrv:latest .

if [ "$STAGE" -eq 2 ]; then
    echo "Stage 2 complete. Run 'docker run --rm php-sqlsrv:latest' to test."
    exit 0
fi

# Stage 3: Laravel (Multi-stage build)
echo "Building Stage 3: Laravel Application (Multi-stage build)..."
cd "$STAGES_DIR/3-laravel"
docker build $NO_CACHE --platform=linux/amd64 -t laravel-app-php8319-sqlsrv:latest -f Dockerfile "$PROJECT_ROOT"

if [ "$STAGE" -eq 3 ]; then
    echo "Stage 3 complete. Run 'docker-compose -f docker-compose-production.yml up' to start the application."
    exit 0
fi

# Stage 4: Xdebug
echo "Building Stage 4: Adding Xdebug for development..."
cd "$STAGES_DIR/4-xdebug"
docker build $NO_CACHE --platform=linux/amd64 -t laravel-app-php8319-sqlsrv-xdebug:latest .

echo "All stages built successfully!"
echo "The production image is 'laravel-app-php8319-sqlsrv:latest' (Stage 3)."
echo "The development image with Xdebug is 'laravel-app-php8319-sqlsrv-xdebug:latest' (Stage 4)."
echo "To run the Laravel application with SQL Server in production, use docker-compose-production.yml."
echo "To run the Laravel application with SQL Server in development, use docker-compose.yml." 