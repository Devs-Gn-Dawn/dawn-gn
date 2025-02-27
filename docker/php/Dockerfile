FROM php:8.2-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configuration et installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \
    zip \
    gd \
    opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation de Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configuration de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Configuration d'Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/app

# Configuration des permissions pour le répertoire var
RUN mkdir -p /var/www/app/var && \
    chown -R www-data:www-data /var/www/app/var && \
    chmod -R 775 /var/www/app/var 