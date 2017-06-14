FROM ubuntu:16.10

MAINTAINER Highco

RUN apt-get update && apt-get install -y apache2 acl
RUN a2enmod proxy_fcgi
RUN a2enmod rewrite
RUN a2enmod ssl

ADD conf/app.conf /etc/apache2/sites-available/
RUN a2ensite app

RUN mkdir -p /var/www

WORKDIR /var/www

RUN mkdir /etc/apache2/ssl

ADD server.crt /etc/apache2/ssl/server.crt
ADD server.key /etc/apache2/ssl/server.key

ADD entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]