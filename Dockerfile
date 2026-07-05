FROM php:8.2-apache

# Cambiar el puerto de Apache internamente al 10000 (el que Render quiere)
RUN sed -i 's/Listen 80/Listen 10000/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:10000>/g' /etc/apache2/sites-available/000-default.conf

# Copiar tus archivos al directorio web
COPY . /var/www/html/

# Asegurar permisos correctos para que carguen todas las imágenes y logos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 10000
