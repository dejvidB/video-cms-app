FROM php:8.2-apache

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install redis && docker-php-ext-enable redis

# Enable Apache Rewrite module (not needed yet, but useful)
RUN a2enmod rewrite

# Set working directory (safe to keep this)
WORKDIR /var/www/html

# Copy everything in the project
COPY . /var/www/html

# Change Apache DocumentRoot to 'public/'
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Allow .htaccess override
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
