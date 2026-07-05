FROM php:8.2-apache

# Copia los archivos del proyecto
COPY . /var/www/html/

# Asegura que Apache sea el dueño de los archivos y pueda leerlos correctamente
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
