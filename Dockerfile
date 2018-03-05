FROM php:7.2.1-cli-stretch
RUN apt-get update && apt-get install -y \
        zlib1g-dev \
        && docker-php-ext-install zip
WORKDIR /app
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY . ./

RUN composer install -o

RUN mkdir -p var && chmod 777 var

EXPOSE 80

CMD ["php", "-S",  "0.0.0.0:80"]
