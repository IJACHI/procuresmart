# ==========================================
# Stage 1: Build Frontend and Vendor Packages
# ==========================================
FROM php:8.3-cli-bookworm AS builder

ENV DEBIAN_FRONTEND=noninteractive
ENV NODE_OPTIONS="--max-old-space-size=2048"

# Install build dependencies, PHP build extensions, and Node.js
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd zip xml exif \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install -g grunt-cli \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy all project files
COPY . .

# Install npm dependencies without premature lifecycle scripts
RUN npm install --ignore-scripts --no-audit --no-fund

# Install PHP dependencies without dev packages
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader --ignore-platform-reqs

# Set version in config, build frontend assets, and include dev seed script
RUN php dev/set-config-params.php \
    && grunt offline \
    && mv /app/build/EspoCRM-* /app/build/dist \
    && mkdir -p /app/build/dist/dev /app/build/dist/bin \
    && cp dev/seed-demo-data.php /app/build/dist/dev/seed-demo-data.php \
    && cp bin/reseed /app/build/dist/bin/reseed \
    && chmod +x /app/build/dist/bin/reseed

# ==========================================
# Stage 2: Production Apache + MariaDB Runtime
# ==========================================
FROM php:8.3-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive

# Install runtime dependencies, PHP extensions, and lightweight MariaDB
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libxml2-dev \
    libpq-dev \
    libldap2-dev \
    mariadb-server \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure ldap --with-libdir="lib/$(dpkg-architecture --query DEB_BUILD_MULTIARCH)" \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        pdo_pgsql \
        zip \
        xml \
        exif \
        bcmath \
        ldap \
        opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Configure ultra-low memory settings for MariaDB (fits comfortably in Render 512MB RAM)
RUN mkdir -p /etc/mysql/mariadb.conf.d && { \
    echo '[mysqld]'; \
    echo 'performance_schema = OFF'; \
    echo 'innodb_buffer_pool_size = 32M'; \
    echo 'innodb_log_buffer_size = 4M'; \
    echo 'innodb_log_file_size = 16M'; \
    echo 'key_buffer_size = 8M'; \
    echo 'max_connections = 25'; \
    echo 'skip-name-resolve'; \
} > /etc/mysql/mariadb.conf.d/99-low-memory.cnf

# Configure Apache prefork MPM for low memory footprint
RUN { \
    echo '<IfModule mpm_prefork_module>'; \
    echo '    StartServers             2'; \
    echo '    MinSpareServers          1'; \
    echo '    MaxSpareServers          3'; \
    echo '    MaxRequestWorkers        10'; \
    echo '    MaxConnectionsPerChild   1000'; \
    echo '</IfModule>'; \
} > /etc/apache2/mods-available/mpm_prefork.conf

# Configure PHP settings recommended for EspoCRM on Render Free Tier
RUN { \
    echo 'memory_limit = 128M'; \
    echo 'max_execution_time = 180'; \
    echo 'post_max_size = 50M'; \
    echo 'upload_max_filesize = 50M'; \
    echo 'date.timezone = UTC'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 64'; \
    echo 'opcache.interned_strings_buffer = 8'; \
    echo 'opcache.max_accelerated_files = 10000'; \
    echo 'opcache.revalidate_freq = 2'; \
    echo 'pdo_mysql.default_socket = /run/mysqld/mysqld.sock'; \
    echo 'mysqli.default_socket = /run/mysqld/mysqld.sock'; \
} > /usr/local/etc/php/conf.d/espocrm.ini

# Configure Apache virtual host with AllowOverride All
RUN { \
    echo '<VirtualHost *:*>'; \
    echo '    DocumentRoot /var/www/html'; \
    echo '    <Directory /var/www/html>'; \
    echo '        Options -Indexes +FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy compiled application from builder
COPY --from=builder /app/build/dist/ /var/www/html/

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Initialize MariaDB and pre-generate database and demo seed dump at build time
RUN mkdir -p /run/mysqld /var/run/mysqld \
    && chown -R mysql:mysql /run/mysqld /var/run/mysqld \
    && mysqld_safe --skip-syslog & \
    for i in $(seq 1 30); do mysqladmin ping --silent && break || sleep 1; done \
    && ln -sf /run/mysqld/mysqld.sock /tmp/mysql.sock \
    && mariadb -e "CREATE DATABASE IF NOT EXISTS espocrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    && mariadb -e "CREATE USER IF NOT EXISTS 'espocrm'@'localhost' IDENTIFIED BY 'espocrm';" \
    && mariadb -e "CREATE USER IF NOT EXISTS 'espocrm'@'127.0.0.1' IDENTIFIED BY 'espocrm';" \
    && mariadb -e "GRANT ALL PRIVILEGES ON espocrm.* TO 'espocrm'@'localhost';" \
    && mariadb -e "GRANT ALL PRIVILEGES ON espocrm.* TO 'espocrm'@'127.0.0.1';" \
    && mariadb -e "FLUSH PRIVILEGES;" \
    && php bin/command config:populate \
    && php bin/command config:set isInstalled true \
    && php bin/command config:set database.platform "Mysql" \
    && php bin/command config:set database.host "127.0.0.1" \
    && php bin/command config:set database.port 3306 \
    && php bin/command config:set database.dbname "espocrm" \
    && php bin/command config:set database.user "espocrm" \
    && php bin/command config:set database.password "espocrm" \
    && php bin/command config:set "defaultPermissions.user" "www-data" \
    && php bin/command config:set "defaultPermissions.group" "www-data" \
    && php bin/command config:set applicationName "NRS-Procuresmart" \
    && php bin/command rebuild \
    && php bin/command create-admin-user admin \
    && (echo "admin123" | php bin/command setPassword admin) \
    && php dev/seed-demo-data.php \
    && mariadb-dump espocrm > /var/www/html/demo-seed.sql \
    && cp data/config.php /var/www/html/data/config.php.template \
    && mysqladmin shutdown

# Set directory ownership and permissions
RUN mkdir -p /var/www/html/data /var/www/html/custom \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/data /var/www/html/custom

EXPOSE 80 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
