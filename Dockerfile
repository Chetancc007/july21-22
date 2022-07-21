FROM php:7.4-fpm

ENV LIBZIP_CFLAGS \
    LIBZIP_LIBS

RUN apt-get update \
     && apt-get install -y libzip-dev \
     libicu-dev \
     libxslt-dev \
     libpng-dev \
     zlib1g-dev \
     libjpeg-dev \
     libfreetype6-dev \
     build-essential \
     libpcre3 \
     libpcre3-dev \
     zlib1g \
     zlib1g-dev \
     libssl-dev \
     libgd-dev \
     libxml2 \
     libxml2-dev \
     uuid-dev \
     wget \
     supervisor \
     locate \
     nano \
     net-tools \
     cron \
     telnet \
	&& mkdir -p /var/run/nginx

RUN set -xe \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure sockets --enable-sockets \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure xsl \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        zip \
        intl \
        pdo_mysql \
        soap \
        xsl \
        sockets \
        opcache

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

RUN pecl install mailparse \
    && docker-php-ext-enable mailparse

# Configure PHP-FPM
# COPY ./docker-config/php/php.ini /usr/local/etc/php/php.ini
# COPY ./docker-config/php/www.conf /usr/local/etc/php-fpm.d/www.conf
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Nginx
RUN cd /opt \
    && wget http://nginx.org/download/nginx-1.20.2.tar.gz \
    && tar -zxvf nginx-1.20.2.tar.gz \
    && cd nginx-1.20.2 && ./configure --prefix=/var/www/ \
    --sbin-path=/usr/sbin/nginx \
    --conf-path=/etc/nginx/nginx.conf \
    --http-log-path=/var/log/nginx/access.log \
    --error-log-path=/var/log/nginx/error.log --with-pcre  \
    --lock-path=/var/lock/nginx.lock \
    --pid-path=/var/run/nginx.pid \
    --with-http_ssl_module \
    --with-http_image_filter_module=dynamic \
    --modules-path=/etc/nginx/modules \
    --with-http_v2_module \
    --with-stream=dynamic \
    --with-http_addition_module \
    --with-http_mp4_module \
    && make \
    && make install
    
RUN \
  curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-9.21.0.311-linux.tar.gz | tar -C /tmp -zx && \
  export NR_INSTALL_USE_CP_NOT_LN=1 && \
  export NR_INSTALL_SILENT=1 && \
  /tmp/newrelic-php5-*/newrelic-install install && \
  rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
  sed -i \
      -e 's/"REPLACE_WITH_REAL_KEY"/"eu01xxdfa780bd20c41fc89d035ccc626be4NRAL"/' \
      -e 's/newrelic.appname = "PHP Application"/newrelic.appname = "ksa-web-staging"/' \
      -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
      -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
      /usr/local/etc/php/conf.d/newrelic.ini

# Configure nginx
#COPY ./docker-config/nginx/nginx.conf /etc/nginx/nginx.conf
#COPY ./docker-config/nginx/nginx.conf.sample /etc/nginx/nginx.conf.sample
#COPY ./magento2-cors.conf /var/www/html/magento2-cors.conf;


# Setup Supervisor
RUN apt-get -y install supervisor
RUN mkdir -p /var/log/supervisor

# Configure supervisord
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# stdout configuration for nginx logs
#RUN ln -sf /dev/stdout /var/log/nginx/access.log \
#    && ln -sf /dev/stderr /var/log/nginx/error.log

WORKDIR /var/www/html

#COPY ./auth.json /var/www/html/auth.json
COPY ./composer.json /var/www/html/composer.json

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN /usr/local/bin/composer install --optimize-autoloader  --ignore-platform-reqs

COPY . .

#Run sh setup.sh

RUN chown -R www-data:www-data /var/www/html/

EXPOSE 5000 80

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
