# # Stage 3: Build environment with PHP and SQL Server extensions
FROM --platform=linux/amd64 php-sqlsrv:latest

# # Install additional dependencies for Laravel and Composer
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# # Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # Create www-data user if it doesn't exist
RUN getent group www-data || groupadd -g 82 www-data && \
    getent passwd www-data || useradd -u 82 -g www-data -s /bin/bash -d /var/www www-data

# # Copy configuration files
COPY docker/stages/3-laravel/config/nginx.conf /etc/nginx/sites-available/default
COPY docker/stages/3-laravel/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/stages/3-laravel/config/php-fpm.conf /usr/local/php/etc/php-fpm.conf
COPY docker/stages/3-laravel/config/php.ini /usr/local/php/etc/php.ini

# # Make sure the php-fpm.conf exists by copying from default if needed
RUN if [ ! -f /usr/local/php/etc/php-fpm.conf ]; then \
    if [ -f /usr/local/php/etc/php-fpm.conf.default ]; then \
        cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf; \
    fi \
fi

# # Enable the Nginx site
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# # Set up Laravel directory structure
WORKDIR /var/www/html
RUN mkdir -p app bootstrap config database public resources routes storage tests \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

# # Copy a simple index.php for testing

# # For a full Laravel application, uncomment these:
COPY app /var/www/html/app/
COPY bootstrap /var/www/html/bootstrap/
COPY config /var/www/html/config/
COPY database /var/www/html/database/
COPY public /var/www/html/public/
COPY resources /var/www/html/resources/
COPY routes /var/www/html/routes/
COPY storage /var/www/html/storage/
COPY tests /var/www/html/tests/
COPY artisan composer.json composer.lock .env.example /var/www/html/
RUN composer install --optimize-autoloader --no-dev
RUN cp .env.example .env
# Generate a valid APP_KEY and update the .env file
RUN php artisan key:generate --force

# # Set up permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# # Stage 4: Production image
FROM --platform=linux/amd64 debian:12-slim

# # Install runtime dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    bash \
    unixodbc \
    libxml2 \
    libonig5 \
    libcurl4 \
    libzip4 \
    libjpeg62-turbo \
    libpng16-16 \
    libfreetype6 \
    libicu72 \
    libsodium23 \
    mariadb-client \
    gnupg \
    lsb-release \
    curl \
    wget \
    apt-transport-https \
    && rm -rf /var/lib/apt/lists/*

# Install Microsoft repository and keys using newer method
RUN mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/keyrings/microsoft.gpg && \
    echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update

# Install Microsoft ODBC Driver for SQL Server
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# # Copy files from builder stage
COPY --from=0 /usr/local/php /usr/local/php
COPY --from=0 /etc/nginx /etc/nginx
COPY --from=0 /etc/supervisor /etc/supervisor
COPY --from=0 /var/www/html /var/www/html
COPY --from=0 /usr/local/bin/php-with-sqlsrv /usr/local/bin/php-with-sqlsrv
COPY --from=0 /usr/local/bin/composer /usr/local/bin/composer

# # Create symbolic links for libraries and executables
RUN ln -s /usr/local/php/bin/php /usr/local/bin/php && \
    ln -s /usr/local/php/sbin/php-fpm /usr/local/bin/php-fpm && \
    ln -s /usr/local/php/sbin/php-fpm /usr/local/sbin/php-fpm

# Set PATH for Nginx and PHP
ENV PATH=$PATH:/usr/local/php/bin:/usr/local/php/sbin

# Disable PHP sessions completely since this API doesn't use them
RUN sed -i '/^session/d' /usr/local/php/etc/php.ini && \
    echo "; Sessions disabled" >> /usr/local/php/etc/php.ini && \
    echo "session.auto_start = 0" >> /usr/local/php/etc/php.ini

# Copy entrypoint script
COPY docker/stages/3-laravel/scripts/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create the Nginx log directories and symlinks
RUN mkdir -p /var/log/nginx && \
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

# Create www-data user if it doesn't exist in the production image
RUN getent group www-data || groupadd -g 82 www-data && \
    getent passwd www-data || useradd -u 82 -g www-data -s /bin/bash -d /var/www www-data

# Create necessary directories for Nginx on Debian
RUN mkdir -p /etc/nginx/sites-enabled

# Make sure php-fpm.conf exists in final image
RUN if [ ! -f /usr/local/php/etc/php-fpm.conf ]; then \
    if [ -f /usr/local/php/etc/php-fpm.conf.default ]; then \
        cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf; \
    fi \
fi

# Verify PHP-FPM configuration
RUN /usr/local/php/sbin/php-fpm -t || { echo "PHP-FPM configuration test failed"; exit 1; }

# Expose port 80 for Nginx
EXPOSE 80

# Start services using supervisord
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"] 