FROM php:5.6.15-apache

RUN apt-get update \
  && apt-get install php5-mysql -y \
    libmcrypt-dev \
    libxml2-dev \
    supervisor \
  && docker-php-ext-install pdo_mysql \
    mcrypt \
    bcmath \
    mbstring

RUN a2enmod rewrite \
  && php5enmod mysqli \
  && php5enmod pdo_mysql

COPY php.ini /usr/local/etc/php/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
CMD ["/usr/bin/supervisord"]
