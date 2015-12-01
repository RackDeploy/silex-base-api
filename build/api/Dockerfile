FROM php:5.6.15-apache

RUN groupadd -r redis && useradd -r -g redis redis

# grab gosu for easy step-down from root
# RUN gpg --keyserver p80.pool.sks-keyservers.net:80 --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4
RUN curl -o /usr/local/bin/gosu -SL "https://github.com/tianon/gosu/releases/download/1.2/gosu-$(dpkg --print-architecture)" \
	&& curl -o /usr/local/bin/gosu.asc -SL "https://github.com/tianon/gosu/releases/download/1.2/gosu-$(dpkg --print-architecture).asc" \
	&& chmod +x /usr/local/bin/gosu

ENV REDIS_VERSION 3.0.5
ENV REDIS_DOWNLOAD_URL http://download.redis.io/releases/redis-3.0.5.tar.gz
ENV REDIS_DOWNLOAD_SHA1 ad3ee178c42bfcfd310c72bbddffbbe35db9b4a6
ENV PHPREDIS_VERSION 2.2.7

RUN apt-get update \
  && apt-get install php5-mysql -y \
    libmcrypt-dev \
    libxml2-dev \
    supervisor \
  && docker-php-ext-install pdo_mysql \
    mcrypt \
    bcmath \
    mbstring \
    mysqli

RUN buildDeps='gcc libc6-dev make' \
	&& set -x \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends \
	&& rm -rf /var/lib/apt/lists/* \
	&& mkdir -p /usr/src/redis \
	&& curl -sSL "$REDIS_DOWNLOAD_URL" -o redis.tar.gz \
	&& echo "$REDIS_DOWNLOAD_SHA1 *redis.tar.gz" | sha1sum -c - \
	&& tar -xzf redis.tar.gz -C /usr/src/redis --strip-components=1 \
	&& rm redis.tar.gz \
	&& make -C /usr/src/redis \
	&& make -C /usr/src/redis install \
	&& rm -r /usr/src/redis

RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz \
    && tar xfz /tmp/redis.tar.gz \
    && rm -r /tmp/redis.tar.gz \
    && mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
    && docker-php-ext-install redis

RUN apt-get purge -y --auto-remove $buildDeps

RUN mkdir /data && chown redis:redis /data

RUN a2enmod rewrite

COPY php.ini /usr/local/etc/php/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

CMD ["/usr/bin/supervisord"]
