FROM php:7.2-apache

ARG DEBIAN_FRONTEND=noninteractive
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# https://github.com/hadolint/hadolint/wiki/DL3008#pin-versions-in-apt-get-install
# hadolint ignore=DL3008
RUN apt-get update && \
  apt-get install -y --no-install-recommends \
    debconf-utils \
    git \
    gnupg \
    locales \
    lsb-release \
    unzip \
    wget \
    zip \
    zlib1g-dev \
    libzstd-dev \
  && echo 'LANG="en_US.UTF-8"' > /etc/default/locale \
  && echo 'en_US.UTF-8 UTF-8' > /etc/locale.gen \
  && LANG=en_US.UTF-8 locale-gen --purge en_US.UTF-8 \
  && dpkg-reconfigure --frontend=noninteractive locales \
  && \
  curl -LO https://dev.mysql.com/get/mysql-apt-config_0.8.14-1_all.deb && \
  echo "mysql-apt-config mysql-apt-config/select-server select mysql-5.7"            | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/dmr-warning note"                          | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/repo-codename select stretch"              | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/repo-distro select debian"                 | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/repo-url string http://repo.mysql.com/apt" | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/preview-component string"                  | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/select-preview select Disabled"            | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/select-tools select Enabled"               | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/select-product select Ok"                  | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/unsupported-platform select abort"         | debconf-set-selections && \
  echo "mysql-apt-config mysql-apt-config/tools-component string mysql-tools"        | debconf-set-selections && \
  apt-key adv --keyserver keyserver.ubuntu.com --recv-keys A4A9406876FCBD3C456770C88C718D3B5072E1F5 && \
  dpkg -i mysql-apt-config_0.8.14-1_all.deb && \
  rm -rf mysql-apt-config_0.8.14-1_all.deb && \
  apt-get update && \
  apt-get install -y --no-install-recommends \
    mysql-community-client \
  && \
  echo "################# Installing php modules ################" && \
  docker-php-ext-install pdo_mysql && \
  apt-get autoremove -y && \
  apt-get autoclean && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/*


RUN a2enmod headers && \
    a2enmod include && \
    a2enmod remoteip && \
    a2enmod rewrite && \
    a2enmod unique_id && \
    a2enmod info

ENV APACHE_LOG_LEVEL info

WORKDIR /app

RUN cd /tmp && wget https://github.com/City-of-Bloomington/uReport/releases/download/2.1.2/ureport-2.1.2.tar.gz && \
  tar -xzvf ureport-2.1.2.tar.gz -C /app/
