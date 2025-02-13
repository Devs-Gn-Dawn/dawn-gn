FROM php:8.2-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Installation de Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration d'Apache
ENV APACHE_DOCUMENT_ROOT /var/www/app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Configuration de PHP pour le développement
COPY php.ini-development "$PHP_INI_DIR/php.ini"
RUN echo "xdebug.mode=debug,develop" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.client_host=host.docker.internal" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.start_with_request=yes" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

# Configuration PHP pour Symfony
RUN echo "memory_limit=256M" >> "$PHP_INI_DIR/conf.d/symfony.ini" \
    && echo "upload_max_filesize=64M" >> "$PHP_INI_DIR/conf.d/symfony.ini" \
    && echo "post_max_size=64M" >> "$PHP_INI_DIR/conf.d/symfony.ini" \
    && echo "date.timezone=Europe/Paris" >> "$PHP_INI_DIR/conf.d/symfony.ini"

WORKDIR /var/www/app

# S'assurer que les répertoires existent et ont les bonnes permissions
RUN mkdir -p /var/www/app/var /var/www/app/public /var/www/.composer && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www && \
    chmod -R 777 /var/www/app/var

USER www-data

# Exposition du port 80
EXPOSE 80 