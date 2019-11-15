FROM debian:stable-slim

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get dist-upgrade -y
RUN apt autoremove -y

# Avoid ERROR: invoke-rc.d: policy-rc.d denied execution of start.
RUN sed -i "s/^exit 101$/exit 0/" /usr/sbin/policy-rc.d

# Locales
RUN apt-get install locales -y
RUN rm -rf /var/lib/apt/lists/*
RUN localedef -i en_US -c -f UTF-8 -A /usr/share/locale/locale.alias en_US.UTF-8
ENV LANG en_US.utf8
RUN apt-get update

# Nginx
RUN apt-get install nginx -y
RUN rm /etc/nginx/conf.d/default.conf
RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80 443

# Php
RUN apt-get install php-fpm -y

WORKDIR /var/www

STOPSIGNAL SIGTERM

# End
CMD ["nginx", "-g", "daemon off;"]

#RUN apt-get install php -y

#RUN apt-get install gnupg2 -y
#RUN apt-get install curl -y

# # MYSQL
#RUN docker-php-ext-configure pdo_mysql
#RUN docker-php-ext-configure opcache
#RUN docker-php-ext-install pdo_mysql

#RUN docker-php-ext-configure pdo_mysql
#RUN docker-php-ext-configure opcache
