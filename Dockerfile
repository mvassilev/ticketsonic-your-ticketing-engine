FROM wordpress:php8.1-apache

# Install dependencies for Composer
RUN apt-get update && apt-get install -y git unzip

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

# Copy plugin files into the image
COPY . /var/www/html/wp-content/plugins/ticketsonic-your-ticketing-engine

# Run composer install in the plugin directory
RUN cd /var/www/html/wp-content/plugins/ticketsonic-your-ticketing-engine && composer install --no-dev --optimize-autoloader