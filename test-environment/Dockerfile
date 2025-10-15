FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    inotify-tools \
    rsync \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Xdebug for debugging
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy test environment files
COPY test-environment/ ./test-environment/

# Copy Xdebug configuration
COPY test-environment/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install file watcher
RUN apt-get update && apt-get install -y \
    inotify-tools \
    rsync \
    && rm -rf /var/lib/apt/lists/*

# Create sync script for file watching
COPY test-environment/sync-changes.sh /usr/local/bin/sync-changes.sh
RUN chmod +x /usr/local/bin/sync-changes.sh

# Expose port
EXPOSE 8081

# Start PHP server and file watcher
CMD ["sh", "-c", "php -S 0.0.0.0:8081 -t test-environment & /usr/local/bin/sync-changes.sh"]
