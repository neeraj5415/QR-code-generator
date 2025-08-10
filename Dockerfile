# Use official PHP Apache image
FROM php:8.2-apache

# Copy project files into Apache's public folder
COPY . /var/www/html/

# Install PHP extensions if needed (example: mysqli)
RUN docker-php-ext-install mysqli && a2enmod rewrite
