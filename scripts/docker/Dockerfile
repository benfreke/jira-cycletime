FROM benfreke/php:8.1.4-pgsql

USER root

# We need this for Carbon time plugin
RUN docker-php-ext-configure calendar && docker-php-ext-install calendar

USER appuser
