# Stage 1: Composer Dependencies
FROM php:8.3-cli-alpine AS composer

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies needed for exif
RUN apk add --no-cache libexif-dev \
  && docker-php-ext-install exif

WORKDIR /app

# Copy only composer files first (for better caching)
COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --no-scripts \
  --no-autoloader \
  --prefer-dist

# Copy only necessary application files (not storage, tests, etc.)
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./

RUN composer dump-autoload --optimize --no-dev

# Stage 2: Node Dependencies and Build
FROM node:20-alpine AS node

WORKDIR /app

# Copy package files first (for better caching)
COPY package.json package-lock.json ./

# Use npm cache mount for faster builds
RUN --mount=type=cache,target=/root/.npm \
  npm ci --prefer-offline

# Copy only files needed for frontend build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Copy vendor for potential Laravel Mix/Vite dependencies
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
  postgresql16-dev \
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

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy built assets from node stage
COPY --from=node /app/public ./public

# Copy only necessary application files
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY artisan ./

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
