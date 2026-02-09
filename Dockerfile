# Imagen base: PHP 8.2 con Apache preconfigurado
FROM php:8.2-apache

# Metadatos
LABEL maintainer="ticketing-app"
LABEL description="Aplicación de gestión de tickets con PHP y Apache"

# Instalar extensiones de PHP necesarias
# pdo_mysql: para conectar con MariaDB mediante PDO
RUN docker-php-ext-install pdo_mysql

# Habilitar módulo rewrite de Apache
# Necesario para URLs limpias y redirecciones
RUN a2enmod rewrite

# Cambiar el DocumentRoot de Apache a /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/>|<Directory /var/www/html/public>|g' /etc/apache2/apache2.conf

# Configurar Apache para interpretar archivos PHP
RUN echo '<FilesMatch \.php$>\n\
    SetHandler application/x-httpd-php\n\
</FilesMatch>' > /etc/apache2/conf-available/php-fpm.conf \
    && a2enconf php-fpm

# Configurar Apache para permitir .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar código fuente (opcional en desarrollo, ya que usamos volumen)
# COPY ./src /var/www/html

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando por defecto (Apache en primer plano)
CMD ["apache2-foreground"]
