FROM --platform=linux/amd64 debian:12-slim

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies
RUN apt-get update && apt-get install -y \
    autoconf \
    bison \
    build-essential \
    cmake \
    curl \
    git \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libonig-dev \
    libpng-dev \
    libsodium-dev \
    libsqlite3-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    pkg-config \
    re2c \
    unzip \
    zlib1g-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set up build directory
WORKDIR /usr/src

# Download and compile PHP
RUN git clone --depth 1 --branch PHP-8.3.19 https://github.com/php/php-src.git && \
    cd php-src && \
    ./buildconf --force && \
    ./configure \
        --prefix=/usr/local/php \
        --with-config-file-path=/usr/local/php/etc \
        --with-config-file-scan-dir=/usr/local/php/etc/conf.d \
        --enable-fpm \
        --enable-mbstring \
        --enable-opcache \
        --enable-intl \
        --enable-pcntl \
        --with-zip \
        --with-curl \
        --with-openssl \
        --with-zlib \
        --with-sodium \
        --enable-gd \
        --with-jpeg \
        --with-freetype \
        --enable-exif \
        --enable-bcmath \
        --with-pdo-mysql && \
    make -j$(nproc) && \
    make install && \
    cp php.ini-production /usr/local/php/etc/php.ini.original && \
    cp php.ini-production /usr/local/php/etc/php.ini && \
    cd .. && \
    rm -rf php-src

# Create directories for PHP configuration
RUN mkdir -p /usr/local/bin /usr/local/sbin /usr/local/php/etc/conf.d && \
    ln -sf /usr/local/php/bin/php /usr/local/bin/php && \
    ln -sf /usr/local/php/bin/phpize /usr/local/bin/phpize && \
    ln -sf /usr/local/php/bin/php-config /usr/local/bin/php-config && \
    ln -sf /usr/local/php/sbin/php-fpm /usr/local/sbin/php-fpm

# Create a test script
RUN echo '<?php echo "PHP Version: " . phpversion() . "\n"; phpinfo(); ?>' > /usr/local/bin/test-php.php && \
    chmod +x /usr/local/bin/test-php.php

# Default command
CMD ["/usr/local/bin/php", "/usr/local/bin/test-php.php"] 