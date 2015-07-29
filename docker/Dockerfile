from php:7-apache

run apt-get -y update && \
 2>&1 apt-get install -y git wget

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        zlib1g-dev \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip

run 2>&1 wget https://phar.phpunit.de/phpunit.phar && \
 mv phpunit.phar /usr/local/bin/phpunit && \
 chmod +x /usr/local/bin/phpunit
