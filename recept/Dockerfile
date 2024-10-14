FROM php:5.6-apache

RUN rm /etc/apt/sources.list

RUN rm /etc/apt/sources.list

RUN echo "deb [trusted=yes] http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list.d/stretch.list
RUN echo "deb [trusted=yes] http://archive.debian.org/debian stretch main" >> /etc/apt/sources.list.d/stretch.list

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y
