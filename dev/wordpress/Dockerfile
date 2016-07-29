FROM php:5.6-apache

COPY php.ini /usr/local/etc/php/

RUN a2enmod rewrite expires

# install the PHP extensions we need
RUN apt-get update && apt-get install -y wget libpng12-dev libjpeg-dev mysql-client nano && rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
	&& docker-php-ext-install gd mysql mysqli

# blackfire agent
RUN wget -O - https://packagecloud.io/gpg.key | apt-key add -
RUN echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list
RUN apt-get update && apt-get install -y blackfire-agent blackfire-php

VOLUME /var/www/html

RUN curl -o /bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
	&& chmod +x /bin/wp \
	&& wp --info --allow-root

ENV WP_VERSION 4.5.2
ENV TZ Europe/Paris

COPY entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
# ENTRYPOINT resets CMD now
CMD ["apache2-foreground"]
