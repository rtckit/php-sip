FROM composer:2.1.3 as composer

WORKDIR /opt/php-sip

COPY composer.* /opt/php-sip/

RUN composer install --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader

COPY . /opt/php-sip

RUN composer dump-autoload --optimize --classmap-authoritative

FROM php:7-cli-alpine

# Build and install pcov
ARG PHP_PCOV_RELEASE=3546be8
RUN cd /tmp && \
  curl https://codeload.github.com/krakjoe/pcov/tar.gz/$PHP_PCOV_RELEASE | tar xvz && \
  cd /tmp/pcov-$PHP_PCOV_RELEASE && \
  apk --no-cache add $PHPIZE_DEPS && \
  phpize && \
  ./configure && \
  make && \
  make install && \
  echo "extension=pcov.so" > /usr/local/etc/php/conf.d/pcov.ini
# Remove build dependencies
RUN apk --purge del $PHPIZE_DEPS && \
  rm -rf /tmp/*

WORKDIR /opt/php-sip

COPY . /opt/php-sip

COPY --from=composer /opt/php-sip/vendor /opt/php-sip/vendor

CMD ["php", "-a"]
