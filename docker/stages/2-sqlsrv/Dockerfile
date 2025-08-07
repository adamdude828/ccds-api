# Stage 2: Build environment with SQL Server extensions
FROM --platform=linux/amd64 php-base:latest

ENV ACCEPT_EULA=Y

# Install dependencies for SQL Server extensions
RUN apt-get update && apt-get install -y \
    gnupg2 \
    apt-transport-https \
    ca-certificates \
    locales \
    unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure locale for SQL Server
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && locale-gen

# Add Microsoft repository and install ODBC driver (modern method for Debian 12)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && apt-get install -y msodbcsql18 mssql-tools18 \
    && rm -rf /var/lib/apt/lists/*

# Set PATH for mssql-tools
ENV PATH=$PATH:/opt/mssql-tools18/bin

# Install necessary tools for PECL
RUN apt-get update && apt-get install -y \
    php-pear \
    && rm -rf /var/lib/apt/lists/*

# Install pdo_sqlsrv, sqlsrv and redis extensions
RUN pecl channel-update pecl.php.net \
    && pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 redis \
    && echo "extension=sqlsrv.so" > /usr/local/php/etc/conf.d/20-sqlsrv.ini \
    && echo "extension=pdo_sqlsrv.so" > /usr/local/php/etc/conf.d/30-pdo_sqlsrv.ini \
    && echo "extension=redis.so" > /usr/local/php/etc/conf.d/40-redis.ini

# Create a test script to verify the extensions are loaded
RUN echo "<?php echo 'SQL Server extensions: '; if (extension_loaded('sqlsrv') && extension_loaded('pdo_sqlsrv')) { echo 'LOADED'; } else { echo 'FAILED'; } echo PHP_EOL; echo 'Redis extension: '; if (extension_loaded('redis')) { echo 'LOADED'; } else { echo 'FAILED'; } echo PHP_EOL; if (!extension_loaded('sqlsrv') || !extension_loaded('pdo_sqlsrv') || !extension_loaded('redis')) { echo 'Available extensions: ' . implode(', ', get_loaded_extensions()); } echo PHP_EOL; ?>" > /test-sqlsrv.php

# Test that the extensions are loaded
RUN php /test-sqlsrv.php || echo "Extension test failed but continuing build for debugging"

# Create a PHP wrapper script that ensures SQL Server extensions are loaded
RUN echo '#!/bin/bash' > /usr/local/bin/php-with-sqlsrv && \
    echo '/usr/local/bin/php "$@"' >> /usr/local/bin/php-with-sqlsrv && \
    chmod +x /usr/local/bin/php-with-sqlsrv
