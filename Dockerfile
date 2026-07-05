FROM php:8.2-apache

# Copia todos los archivos de tu repositorio al directorio web del servidor
COPY . /var/www/html/

# Expone el puerto 80 para el tráfico web
EXPOSE 80
