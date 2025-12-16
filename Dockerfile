# Stage 1: Composer Dependencies
FROM php:8.3-cli-alpine AS composer

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies needed for exif
RUN apk add --no-cache libexif-dev \
  && docker-php-ext-install exif

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --no-scripts \
  --no-autoloader \
  --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev

# Stage 2: Node Dependencies and Build
FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# Stage 3: Production Image
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
  nginx \
  supervisor \
  mariadb-client \
  postgresql16-client \
  libpng-dev \
  libjpeg-turbo-dev \
  libwebp-dev \
  freetype-dev \
  libzip-dev \
  oniguruma-dev \
  icu-dev \
  libexif-dev \
  bash \
  curl \
  git \
  && docker-php-ext-configure gd \
  --with-freetype \
  --with-jpeg \
  --with-webp \
  && docker-php-ext-install -j$(nproc) \
  pdo_mysql \
  pdo_pgsql \
  gd \
  zip \
  intl \
  mbstring \
  opcache \
  bcmath \
  exif \
  pcntl

# Install Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del pcre-dev $PHPIZE_DEPS

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/php.ini $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --from=composer /app/vendor ./vendor
COPY --from=node /app/public ./public
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html/storage \
  && chmod -R 755 /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
