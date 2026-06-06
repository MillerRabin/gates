FROM php:8.4-cli-trixie-slim

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
      git \
      unzip \
      zip \
      curl \
      libzip-dev \
      libicu-dev \
      libonig-dev \
      default-mysql-client \
    && docker-php-ext-install \
      pdo \
      pdo_mysql \
      bcmath \
      intl \
      zip \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install \
  --no-interaction \
  --prefer-dist

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
