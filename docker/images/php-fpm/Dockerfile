FROM php:7.2-fpm

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_12_1/

RUN apt-get update && apt-get install -qqy git unzip libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libpng-dev \
        libaio1 wget && apt-get clean autoclean && apt-get autoremove --yes &&  rm -rf /var/lib/{apt,dpkg,cache,log}/ 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


RUN mkdir /opt/oracle \
    && cd /opt/oracle     
    
ADD instantclient-basic-linux.x64-12.1.0.2.0.zip /opt/oracle
ADD instantclient-sdk-linux.x64-12.1.0.2.0.zip /opt/oracle


RUN  unzip /opt/oracle/instantclient-basic-linux.x64-12.1.0.2.0.zip -d /opt/oracle \
    && unzip /opt/oracle/instantclient-sdk-linux.x64-12.1.0.2.0.zip -d /opt/oracle \
    && ln -s /opt/oracle/instantclient_12_1/libclntsh.so.12.1 /opt/oracle/instantclient_12_1/libclntsh.so \
    && ln -s /opt/oracle/instantclient_12_1/libclntshcore.so.12.1 /opt/oracle/instantclient_12_1/libclntshcore.so \
    && ln -s /opt/oracle/instantclient_12_1/libocci.so.12.1 /opt/oracle/instantclient_12_1/libocci.so \
    && rm -rf /opt/oracle/*.zip
    
ENV LD_LIBRARY_PATH  /opt/oracle/instantclient_12_1:${LD_LIBRARY_PATH}
    

RUN echo 'instantclient,/opt/oracle/instantclient_12_1/' | pecl install oci8-2.2.0 \
      && docker-php-ext-enable \
               oci8 \ 
       && docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/opt/oracle/instantclient_12_1,12.1 \
       && docker-php-ext-install \
               pdo_oci \
       && echo /opt/oracle/instantclient_12_1 > /etc/ld.so.conf.d/oracle-instantclient.conf && \
                ldconfig

RUN apt-get install -y --no-install-recommends \
    libboost-dev \
    libprotobuf-dev openssl protobuf-compiler

RUN mkdir -p /usr/src/php/ext && \
    mkdir /tmp/mysql_xdevapi && \
    cd /tmp && \
    pecl download mysql_xdevapi && \
    tar xf mysql_xdevapi-*.tgz -C mysql_xdevapi --strip-components 1 && \
    mv ./mysql_xdevapi /usr/src/php/ext/ && \
    rm /tmp/mysql_xdevapi-*.tgz

RUN docker-php-ext-install mysql_xdevapi

RUN apt-get update -y && apt-get upgrade -y && apt-get install -y \
    curl \
    wget \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libicu-dev \
    libxml2-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    sendmail \
    libpng-dev \
    zlib1g-dev \
    gcc zip \
    && pecl install mcrypt-1.0.1 \
    && pecl install mongodb \
    && pecl install imagick \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install -j$(nproc) intl bcmath soap zip mbstring exif opcache\
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable mcrypt opcache \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install sockets \
    && docker-php-ext-enable sockets

RUN pecl install xdebug-3.0.0 && docker-php-ext-enable xdebug

ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini


ENV TZ=Europe/Moscow

WORKDIR /app

CMD ["php-fpm"]
