# ==============================================================================
# Multi-Stage Dockerfile — Invitation Management Application
# Stage 1: Composer Dependencies
# Stage 2: Node.js / Vite Asset Build
# Stage 3: Production PHP-FPM Image
# ==============================================================================

# ── Stage 1: Composer Dependencies ───────────────────────────────────────────
FROM composer:2 AS composer-deps

WORKDIR /app

# Copy only composer files first for better layer caching
COPY composer.json composer.lock ./

# Copy module composer files (required by wikimedia/composer-merge-plugin)
COPY Modules/UserManagement/composer.json Modules/UserManagement/composer.json
COPY Modules/LogManagement/composer.json Modules/LogManagement/composer.json

# Mark directory as safe for git (avoids dubious ownership error)
RUN git config --global --add safe.directory /app

# Install dependencies without dev packages
# --no-scripts: skip post-autoload-dump (artisan not available in this stage)
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --ignore-platform-reqs

# ── Stage 2: Frontend Assets Build ──────────────────────────────────────────
FROM node:20-alpine AS node-build

WORKDIR /app

# Copy package files first for layer caching
COPY package.json package-lock.json ./

# Install Node.js dependencies
RUN npm install --no-audit --no-fund

# Copy Vite config and source files needed for build
COPY vite.config.js ./
COPY vite-module-loader.js ./
COPY resources/ resources/
COPY Modules/ Modules/

# Build production assets
RUN npm run build

# ── Stage 3: Production Image ───────────────────────────────────────────────
FROM php:8.4-fpm AS production

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Custom PHP configuration
RUN echo "upload_max_filesize = 50M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "post_max_size = 55M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "max_execution_time = 120" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "expose_php = Off" >> "$PHP_INI_DIR/conf.d/custom.ini"

WORKDIR /var/www/html

# Copy Composer binary from Stage 1
COPY --from=composer-deps /usr/bin/composer /usr/bin/composer

# Copy application source code
COPY . .

# Copy Composer dependencies from Stage 1
COPY --from=composer-deps /app/vendor vendor/

# Copy built frontend assets from Stage 2
COPY --from=node-build /app/public/build public/build/

# Run post-install scripts (package:discover, etc.) now that artisan is available
RUN composer dump-autoload --optimize --no-dev --no-interaction --ignore-platform-reqs

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create required storage directories
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/app/private \
    storage/logs \
    bootstrap/cache

# Set ownership and permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    public

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]