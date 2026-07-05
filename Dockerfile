FROM trafex/php-nginx:latest

# Cambia al directorio de trabajo
WORKDIR /var/www/html

# Copia tus archivos
COPY --chown=nginx:nginx . /var/www/html

# Configuramos el puerto 10000 tanto para Nginx como para Render
ENV PORT=10000
EXPOSE 10000

# Modificamos internamente la configuración de Nginx para que escuche en el puerto 10000
RUN sed -i 's/listen \[::\]:8080 default_server;/listen [::]:10000 default_server;/g' /etc/nginx/conf.d/default.conf \
    && sed -i 's/listen 8080 default_server;/listen 10000 default_server;/g' /etc/nginx/conf.d/default.conf
