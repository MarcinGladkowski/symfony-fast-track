FROM php:8.1-fpm

ARG userid
ARG groupid

RUN apt-get update \
  && apt-get install -y libzip-dev libxslt1-dev git wget --no-install-recommends gnupg2 \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

RUN apt-get update && apt install -y libpq-dev libpng-dev && docker-php-ext-install pdo pdo_mysql pdo_pgsql xsl zip dom gd

RUN apt -y update \
	&& apt install -y libicu-dev \
	&& docker-php-ext-configure intl \
	&& docker-php-ext-install intl

RUN pecl install redis

RUN git config --global user.email "example@example.com" \
        && git config --global user.name "Example"

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && architecture=$(uname -m) \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

# Please note that the Blackfire Probe is dependent on the session module.
# If it isn't present in your install, you will need to enable it yourself.

RUN echo $userid

RUN groupadd -g $userid myuser \
  	&& useradd -m -u $userid -g $groupid myuser

USER myuser

WORKDIR /var/www

COPY --from=composer /usr/bin/composer /usr/bin/composer

# build project scratch
# RUN symfony new guestbook --version=6.0 --php=8.1 --webapp --docker --cloud