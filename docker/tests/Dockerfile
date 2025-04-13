FROM php:8.2-cli

# Install PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql sockets
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files (so composer works)
WORKDIR /app
COPY . /app

# Install dependencies (with dev tools for tests)
RUN composer install --no-interaction

# Run PHPUnit by default
CMD ["vendor/bin/phpunit"]
