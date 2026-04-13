FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by CodeIgniter 3 and the app
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        mbstring \
        zip \
        dom \
        simplexml \
        intl \
        opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Ensure writable directories exist with correct permissions
RUN mkdir -p \
        uploads \
        tmp \
        application/cache \
        application/logs \
    && chown -R www-data:www-data \
        uploads \
        tmp \
        application/cache \
        application/logs \
    && chmod -R 775 \
        uploads \
        tmp \
        application/cache \
        application/logs

# PHP configuration overrides
RUN echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "session.save_path = /tmp" >> /usr/local/etc/php/conf.d/app.ini

EXPOSE 80
