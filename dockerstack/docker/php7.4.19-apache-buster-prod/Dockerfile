FROM public.ecr.aws/e9j0q2x3/bac/php-7.4.19-apache-buster

LABEL maintainer="Adrian Coral <adriancoral@gmail.com>"

LABEL so.debian="buster"
LABEL php="7.4.19"

# persistent dependencies
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends

# install the PHP extensions
RUN set -ex; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libxml2-dev \
		libmcrypt-dev \
		zlib1g-dev \
		libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
		libmemcached-dev \
		libzip-dev \
		less \
		wget \
		unzip  \
		locales \
        zip \
        jpegoptim optipng pngquant gifsicle \
        vim \
        curl \
        supervisor && \
        mkdir -p /var/log/supervisor && \
        touch /var/log/supervisor/supervisor.log && \
        chmod 666 /var/log/supervisor/supervisor.log && \
        mkdir -p /etc/supervisor/conf.d

ADD ./dockerstack/etc/supervisor /etc/supervisor/conf.d/

# Install extensions
RUN	docker-php-ext-install mysqli pdo_mysql;
RUN docker-php-ext-install -j "$(nproc)" \
		bcmath \
		mbstring \
		soap \
		sqlite3 \
		opcache \
		exif \
		pcntl \
		gd \
		zip ; \
	pecl install memcached-3.1.5; \
	docker-php-ext-enable memcached;

RUN	docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd;

RUN a2enmod expires \
	&& a2enmod headers \
	&& a2enmod rewrite \
    && a2enmod ssl

# Install composer & php-cs-fixer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o /usr/local/bin/php-cs-fixer && chmod a+x /usr/local/bin/php-cs-fixer

# Add user for laravel application
RUN groupadd -r www -g 1000 && useradd -u 1000 -r -g www -m  www -s /bin/bash && \
    echo "alias t=vendor/bin/phpunit" >> /home/www/.bash_aliases

ADD ./dockerstack/docker/php7.4.19-apache-buster-prod/supervisor.conf /etc/supervisord.conf

ADD ./dockerstack/docker/php7.4.19-apache-buster-prod/startup.sh /etc/startup.sh

# Set working directory
WORKDIR /var/www

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisord.conf"]
