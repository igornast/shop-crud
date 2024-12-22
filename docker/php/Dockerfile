FROM php:8.3-fpm AS app

RUN apt-get clean && apt-get update && apt-get install -y --fix-missing --no-install-recommends \
    zlib1g-dev \
    libmemcached-dev \
    libssl-dev \
    librabbitmq-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    make \
    curl \
    git \
    bash \
    apt-file \
    libxml2-dev \
    libpq-dev \
    openssl

RUN docker-php-ext-install \
    zip \
    pgsql \
    pdo \
    pdo_pgsql \
    soap \
    gettext \
    mbstring \
    bcmath \
    opcache

RUN rm -rf /var/cache/apk/* && rm -rf /tmp/* && rm -rf /var/lib/apt/lists/*
RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

RUN pecl install xdebug-3.4.0 && composer self-update

FROM app AS prod

ENV APP_ENV=prod

WORKDIR /var/www

COPY app/composer.json app/composer.lock app/symfony.lock app/.env ./

COPY app/composer.json app/composer.lock app/symfony.lock ./
COPY app/bin bin/
COPY app/config config/
COPY app/migrations migrations/
COPY app/public public/
COPY app/src src/

RUN composer install --no-dev --prefer-dist --no-progress --no-interaction && \
    /var/www/bin/console cache:warmup

COPY docker/php/bin /usr/local/bin/
RUN chmod -R +x /usr/local/bin/

CMD ["start-fpm"]

FROM app AS local
RUN docker-php-ext-enable xdebug

WORKDIR /var/www
