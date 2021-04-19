FROM php:7.3-cli-stretch

#
# Packages
#

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git zip unzip

RUN apt-get clean \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# Enable debuging
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

#
# Composer
#

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_MEMORY_LIMIT=-1

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Set default workdir
WORKDIR /var/www/html

# Global path
ENV PATH="/var/www/html/vendor/bin:${PATH}"
