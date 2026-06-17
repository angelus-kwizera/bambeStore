FROM php:8.2-apache

# 1. Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libcurl4-openssl-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Enable Apache rewrite engine
RUN a2enmod rewrite

# 3. Copy application source files
COPY . /var/www/html/

# 4. CRITICAL FIX: Disable alternative MPMs AFTER files are copied 
# This prevents errors if custom configurations exist in your codebase
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork || true

# 5. Set up required folders and apply explicit permissions
RUN mkdir -p /var/www/html/uploads/products \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
