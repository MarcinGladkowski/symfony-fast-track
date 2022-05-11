FROM php:8.1-fpm

ARG userid
ARG groupid

RUN apt-get update \
  && apt-get install -y libzip-dev libxslt1-dev git wget --no-install-recommends \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

RUN apt-get update && apt install -y libpq-dev libpng-dev && docker-php-ext-install pdo pdo_mysql pdo_pgsql xsl zip dom gd

RUN apt -y update \
	&& apt install -y libicu-dev \
	&& docker-php-ext-configure intl \
	&& docker-php-ext-install intl

RUN git config --global user.email "example@example.com" \
        && git config --global user.name "Example"

RUN echo $userid

RUN groupadd -g $userid myuser \
  	&& useradd -m -u $userid -g $groupid myuser

USER myuser

WORKDIR /var/www

COPY --from=composer /usr/bin/composer /usr/bin/composer

# build project scratch
# RUN symfony new guestbook --version=6.0 --php=8.1 --webapp --docker --cloud