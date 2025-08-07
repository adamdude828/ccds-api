# Stage 4: Add Xdebug to the Laravel application
FROM --platform=linux/amd64 laravel-app-php8319-sqlsrv:latest

# Install build dependencies
RUN apt-get update && apt-get install -y \
    autoconf \
    g++ \
    make \
    php-pear \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN cd /tmp && \
    pecl download xdebug && \
    tar xzvf xdebug-*.tgz && \
    cd xdebug-* && \
    /usr/local/php/bin/phpize && \
    ./configure --with-php-config=/usr/local/php/bin/php-config && \
    make && \
    make install && \
    cd .. && \
    rm -rf xdebug-*

# Configure Xdebug
RUN mkdir -p /usr/local/php/etc/conf.d && \
    echo "zend_extension=xdebug.so" > /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.mode=develop,debug,coverage" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    echo "xdebug.discover_client_host=true" >> /usr/local/php/etc/conf.d/xdebug.ini && \
    touch /var/log/xdebug.log && \
    chmod 666 /var/log/xdebug.log

# Create a test script to verify Xdebug is loaded
RUN echo "<?php echo 'Xdebug installed: ' . (extension_loaded('xdebug') ? 'YES' : 'NO') . PHP_EOL; ?>" > /var/www/html/xdebug-test.php

# Override supervisor config to disable queue worker in development
COPY supervisord-override.conf /etc/supervisor/conf.d/supervisord.conf 