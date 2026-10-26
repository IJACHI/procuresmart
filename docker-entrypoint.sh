#!/bin/bash
set -e

# Default port to 10000 (Render default) or 80
PORT="${PORT:-10000}"

# Configure Apache to listen on $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Ensure writable directories exist
mkdir -p /var/www/html/data/cache /var/www/html/data/logs /var/www/html/custom

# -------------------------------------------------------------
# 1. Database Handling: Embedded MariaDB or External Database
# -------------------------------------------------------------
if [ -z "${DATABASE_URL}" ] && [ -z "${ESPOCRM_DATABASE_HOST}" ]; then
    echo "Using embedded local MariaDB (ultra-low memory profile)..."
    service mariadb start || mysqld_safe --skip-syslog &

    # Wait for MariaDB to become ready
    for i in $(seq 1 30); do
        if mysqladmin ping --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    # Ensure socket symlink exists for unix socket connections
    ln -sf /run/mysqld/mysqld.sock /tmp/mysql.sock 2>/dev/null || true

    # Ensure database and user exist
    mariadb -e "CREATE DATABASE IF NOT EXISTS espocrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
    mariadb -e "CREATE USER IF NOT EXISTS 'espocrm'@'localhost' IDENTIFIED BY 'espocrm';" 2>/dev/null || true
    mariadb -e "CREATE USER IF NOT EXISTS 'espocrm'@'127.0.0.1' IDENTIFIED BY 'espocrm';" 2>/dev/null || true
    mariadb -e "GRANT ALL PRIVILEGES ON espocrm.* TO 'espocrm'@'localhost';" 2>/dev/null || true
    mariadb -e "GRANT ALL PRIVILEGES ON espocrm.* TO 'espocrm'@'127.0.0.1';" 2>/dev/null || true
    mariadb -e "FLUSH PRIVILEGES;" 2>/dev/null || true

    # Re-seed demo database if demo-seed.sql is present
    if [ -f /var/www/html/demo-seed.sql ]; then
        echo "Reseeding database with fresh procurement demo data..."
        mariadb espocrm < /var/www/html/demo-seed.sql 2>/dev/null || true
    fi

    # Restore config.php template if config.php is missing
    if [ ! -f /var/www/html/data/config.php ] && [ -f /var/www/html/data/config.php.template ]; then
        cp /var/www/html/data/config.php.template /var/www/html/data/config.php
    fi
else
    # External database configuration (e.g. Render PostgreSQL or external MySQL)
    if [ -n "${DATABASE_URL}" ] && [ -z "${ESPOCRM_DATABASE_HOST}" ]; then
        PROTO="$(echo "${DATABASE_URL}" | grep :// | sed -e's,^\(.*://\).*,\1,g')"
        URL_NO_PROTO="${DATABASE_URL/$PROTO/}"
        USER_PASS="$(echo "${URL_NO_PROTO}" | grep @ | cut -d@ -f1)"
        HOST_PORT_DB="${URL_NO_PROTO/$USER_PASS@/}"

        if [ "${PROTO}" = "postgres://" ] || [ "${PROTO}" = "postgresql://" ]; then
            export ESPOCRM_DATABASE_PLATFORM="Postgresql"
        else
            export ESPOCRM_DATABASE_PLATFORM="Mysql"
        fi

        export ESPOCRM_DATABASE_USER="$(echo "${USER_PASS}" | cut -d: -f1)"
        export ESPOCRM_DATABASE_PASSWORD="$(echo "${USER_PASS}" | cut -d: -f2)"
        
        HOST_PORT="$(echo "${HOST_PORT_DB}" | cut -d/ -f1)"
        export ESPOCRM_DATABASE_NAME="$(echo "${HOST_PORT_DB}" | cut -d/ -f2 | cut -d? -f1)"
        export ESPOCRM_DATABASE_HOST="$(echo "${HOST_PORT}" | cut -d: -f1)"
        export ESPOCRM_DATABASE_PORT="$(echo "${HOST_PORT}" | cut -s -d: -f2)"
    fi

    if [ -n "${ESPOCRM_DATABASE_HOST}" ]; then
        export ESPOCRM_DATABASE_PLATFORM="${ESPOCRM_DATABASE_PLATFORM:-Mysql}"
        echo "Configuring external database connection (${ESPOCRM_DATABASE_PLATFORM}) on ${ESPOCRM_DATABASE_HOST}..."

        php bin/command config:populate >/dev/null 2>&1 || true
        php bin/command config:set "defaultPermissions.user" "www-data" >/dev/null 2>&1 || true
        php bin/command config:set "defaultPermissions.group" "www-data" >/dev/null 2>&1 || true
        php bin/command config:set database.platform "${ESPOCRM_DATABASE_PLATFORM}" >/dev/null 2>&1 || true
        php bin/command config:set database.host "${ESPOCRM_DATABASE_HOST}" >/dev/null 2>&1 || true
        if [ -n "${ESPOCRM_DATABASE_PORT}" ]; then
            php bin/command config:set database.port "${ESPOCRM_DATABASE_PORT}" >/dev/null 2>&1 || true
        fi
        php bin/command config:set database.dbname "${ESPOCRM_DATABASE_NAME}" >/dev/null 2>&1 || true
        php bin/command config:set database.user "${ESPOCRM_DATABASE_USER}" >/dev/null 2>&1 || true
        php bin/command config:set database.password "${ESPOCRM_DATABASE_PASSWORD}" >/dev/null 2>&1 || true
    fi
fi

# -------------------------------------------------------------
# 2. Update siteUrl and applicationName
# -------------------------------------------------------------
php bin/command config:set applicationName "NRS-Procuresmart" >/dev/null 2>&1 || true

if [ -n "${RENDER_EXTERNAL_URL}" ]; then
    php bin/command config:set siteUrl "${RENDER_EXTERNAL_URL}" >/dev/null 2>&1 || true
elif [ -n "${ESPOCRM_SITE_URL}" ]; then
    php bin/command config:set siteUrl "${ESPOCRM_SITE_URL}" >/dev/null 2>&1 || true
fi

# Clear any stale application/theme cache and rebuild
echo "Rebuilding metadata and clearing cache..."
php bin/command clear_cache >/dev/null 2>&1 || true
php bin/command rebuild >/dev/null 2>&1 || true

# -------------------------------------------------------------
# 3. Permissions & Start
# -------------------------------------------------------------
chown -R www-data:www-data /var/www/html/data /var/www/html/custom
chmod -R 775 /var/www/html/data /var/www/html/custom

echo "Starting Apache on port ${PORT}..."
exec "$@"
