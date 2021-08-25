FROM php:7.4-fpm

RUN apt-get update -yqq && \
    apt-get install -yqq \
    git \
    curl \
    zip \
    unzip \
    gzip \
    libzip-dev \
    libicu-dev \
    nano

RUN docker-php-ext-configure intl 

RUN docker-php-ext-install intl pdo pdo_mysql zip opcache bcmath sockets

RUN pecl install xdebug && docker-php-ext-enable xdebug opcache

RUN echo "opcache.preload=/app/config/preload.php" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
RUN echo "opcache.preload_user=www-data" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
RUN echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
RUN echo "opcache.max_accelerated_files = 20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
RUN echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/php.ini-production
RUN echo "realpath_cache_size=4096K" >> /usr/local/etc/php/php.ini-production
RUN echo "realpath_cache_ttl=600" >> /usr/local/etc/php/php.ini-production

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

WORKDIR /app
