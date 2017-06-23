FROM ubuntu:16.10

MAINTAINER Highco

RUN apt-get update && apt-get install -y openssh-server git

RUN chsh -s /bin/bash www-data
RUN usermod -m -d /home/www www-data
RUN mkdir -p /home/www/.ssh

RUN mkdir /var/run/sshd

EXPOSE 22

VOLUME '/var/www'

ADD entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
