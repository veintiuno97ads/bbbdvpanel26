FROM trafex/php-nginx:latest

# Cambia al directorio de trabajo por defecto de Nginx
WORKDIR /var/www/html

# Copia todos tus archivos al servidor
COPY --chown=nginx:nginx . /var/www/html

# El puerto por defecto que usa este contenedor ligero es el 8080
EXPOSE 8080
