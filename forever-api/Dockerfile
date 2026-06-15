# 1. Usar imagen oficial de PHP con Apache
FROM php:8.3-apache

# 2. Instalar herramientas y drivers para PostgreSQL y la librería ZIP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev
    
# 3. Limpiar caché de instalaciones para que el servidor sea más rápido
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 4. Instalar extensiones de PHP (pdo_pgsql para BD y zip para Composer)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# 5. Encender el motor de rutas de Apache (Vital para Laravel)
RUN a2enmod rewrite

# 6. Decirle a Apache que la carpeta principal es /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Traer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 8. Movernos a la carpeta de trabajo
WORKDIR /var/www/html

# 9. Copiar todo tu código de Laravel
COPY . .

# 10. Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# 11. Dar permisos para que Laravel pueda guardar fotos y reportes
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Abrir el puerto 80
EXPOSE 80