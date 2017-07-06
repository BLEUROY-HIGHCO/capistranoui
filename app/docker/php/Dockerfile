FROM ubuntu:16.10

MAINTAINER Highco

RUN echo "force-unsafe-io" > /etc/dpkg/dpkg.cfg.d/02apt-speedup
RUN echo "Acquire::http {No-Cache=True;};" > /etc/apt/apt.conf.d/no-cache

RUN apt-get update -y

RUN apt-get install -y curl apt-transport-https

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update -y && apt-get install -y yarn

RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -

RUN apt-get install -y vim zsh nodejs
RUN apt-get install -y git acl
RUN apt-get clean

RUN apt-get install -y software-properties-common python-software-properties
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php

RUN apt-get update -y

RUN apt-get install -y --allow-unauthenticated php7.1-fpm php7.1-cli
RUN apt-get install -y --allow-unauthenticated php7.1-dev php7.1-gd
RUN apt-get install -y --allow-unauthenticated php7.1-curl php7.1-opcache php7.1-mcrypt
RUN apt-get install -y --allow-unauthenticated php7.1-xdebug php7.1-mysql php7.1-intl
RUN apt-get install -y --allow-unauthenticated php7.1-zip php7.1-dom
RUN apt-get install -y bc
RUN apt-get install -y unzip
RUN apt-get install -y wget
RUN apt-get install -y ruby git
RUN gem install bundler
RUN chmod o+w -R /var/lib/gems
RUN chmod o+w -R /usr/local/bin

RUN mkdir -p /var/www
RUN mkdir -p /var/log/phpfpm && chown www-data:www-data /var/log/phpfpm
RUN mkdir -p /var/run/phpfpm && chown www-data:www-data /var/run/phpfpm

RUN php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer\
    && php -r "unlink('composer-setup.php');"

WORKDIR /

ADD xdebug.ini /etc/php/7.1/mods-available/xdebug.ini
ADD fpm-php.ini /etc/php/7.1/fpm/php.ini
ADD cli-php.ini /etc/php/7.1/cli/php.ini


RUN sed -i '/^listen =/c listen = 0.0.0.0:9000' /etc/php/7.1/fpm/pool.d/www.conf
RUN sed -i '/^request_terminate_timeout =/c request_terminate_timeout = 180' /etc/php/7.1/fpm/pool.d/www.conf
RUN sed -i "s@error_log = /var/log/php7.1-fpm.log@error_log = /var/log/phpfpm/php7.1-fpm.error.log@g" /etc/php/7.1/fpm/php-fpm.conf
RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php/7.1/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php/7.1/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php/7.1/fpm/pool.d/www.conf
RUN sed -e 's/;pm\.status_path/pm\.status_path/' -i /etc/php/7.1/fpm/pool.d/www.conf
RUN sed -e 's/;pm\.ping/pm\.ping/' -i /etc/php/7.1/fpm/pool.d/www.conf

RUN echo "daemonize=no" > /etc/php/7.1/fpm/pool.d/daemonize.conf

RUN phpenmod mcrypt
RUN phpenmod zip

RUN npm install -g scss

RUN touch /var/log/phpfpm/php7.1-fpm.error.log
RUN chown -R www-data /var/log/phpfpm/php7.1-fpm.error.log

RUN echo "www-data:linuxpassword" | chpasswd

WORKDIR /var/www

ENV SYMFONY_ENV=dev
ENV SYMFONY_DEBUG=1

EXPOSE 9000

ADD deploy-keys /var/deploy-keys
RUN chown -R www-data:www-data /var/deploy-keys

ADD deploy-keys /var/deploy-keys
RUN chown -R www-data:www-data /var/deploy-keys

RUN bundle config github.https true
RUN bundle config git.allow_insecure true

ADD wait-for-it.sh /usr/local/bin/wait-for-it.sh
RUN chmod u+s /usr/local/bin/wait-for-it.sh
RUN chmod u+x /usr/local/bin/wait-for-it.sh

ADD watch.sh /usr/local/bin/watch.sh
RUN chmod u+s /usr/local/bin/watch.sh
RUN chmod u+x /usr/local/bin/watch.sh

ADD entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
