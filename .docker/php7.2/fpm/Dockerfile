FROM php:7.2-fpm-alpine

ADD https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck /usr/local/bin/php-fpm-healthcheck
HEALTHCHECK --interval=5s --retries=2 --timeout=2s --start-period=1s CMD php-fpm-healthcheck

COPY ./core/ ./include/ ./language/ ./htdocs/ ./libraries/ ./modules/ ./plugins/ ./storage/ ./vendor/ ./*.php ./composer.* ./LICENSE ./XOOPS_copyrights.txt /var/www/html/
COPY ./.docker/php7.2/fpm/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN apk add --no-cache \
        bash \
        fcgi \
        curl && \
    apk add --no-cache \
            curl-dev \
            freetype-dev \
            libffi-dev \
            libjpeg-turbo-dev \
            libwebp-dev \
            libpng-dev \
            libxml2 \
            libmcrypt-dev \
            libxml2-dev \
            zlib-dev \
            libxpm-dev \
            icu-dev && \
    export CFLAGS="-I/usr/src/php" && \
    apk add --no-cache --virtual .build \
            autoconf \
            gcc \
            make \
            build-base && \
    docker-php-source extract && \
    docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-webp-dir=/usr/include/ \
        --with-xpm-dir=/usr/include/ && \
    docker-php-ext-install \
    	json \
    	gd \
    	mbstring \
    	curl \
    	bcmath \
    	opcache \
    	posix \
    	fileinfo \
    	pdo_mysql && \
    docker-php-source delete && \
    export CFLAGS= && \
    apk del .build && \
    chmod 0777 /usr/local/bin/* && \
    touch /usr/local/etc/php/conf.d/max-memory.ini && \
    chmod 0666 /usr/local/etc/php/conf.d/max-memory.ini

EXPOSE 9000

VOLUME /var/www/html/storage/

ENTRYPOINT ["entrypoint.sh"]
CMD ["docker-php-entrypoint", "php-fpm"]