from php:7-apache

run apt-get -y update && \
 2>&1 apt-get install -y git wget

run 2>&1 wget https://phar.phpunit.de/phpunit.phar && \
 mv phpunit.phar /usr/local/bin/phpunit && \
 chmod +x /usr/local/bin/phpunit
