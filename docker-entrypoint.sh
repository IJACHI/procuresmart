#!/bin/bash
set -e

# Default port to 10000 (Render default) or 80
PORT="${PORT:-10000}"

# Configure Apache to listen on $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Ensure writable directories exist and have correct permissions
mkdir -p /var/www/html/data/cache /var/www/html/data/logs /var/www/html/custom
chown -R www-data:www-data /var/www/html/data /var/www/html/custom
chmod -R 775 /var/www/html/data /var/www/html/custom

# Support auto-configuring from DATABASE_URL (common in Render)
if [ -n "${DATABASE_URL}" ] && [ -z "${ESPOCRM_DATABASE_HOST}" ]; then
    # e.g., postgres://user:password@host:port/dbname or mysql://user:password@host:port/dbname
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

# If database credentials are provided and not yet installed, auto-populate config
if [ -n "${ESPOCRM_DATABASE_HOST}" ]; then
    export ESPOCRM_DATABASE_PLATFORM="${ESPOCRM_DATABASE_PLATFORM:-Mysql}"
    echo "Configuring database connection for ${ESPOCRM_DATABASE_PLATFORM} on ${ESPOCRM_DATABASE_HOST}..."

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

    if [ -n "${ESPOCRM_SITE_URL}" ]; then
        php bin/command config:set siteUrl "${ESPOCRM_SITE_URL}" >/dev/null 2>&1 || true
    fi
fi

# Set siteUrl from RENDER_EXTERNAL_URL if set
if [ -n "${RENDER_EXTERNAL_URL}" ] && [ -z "${ESPOCRM_SITE_URL}" ]; then
    php bin/command config:set siteUrl "${RENDER_EXTERNAL_URL}" >/dev/null 2>&1 || true
fi

echo "Starting Apache on port ${PORT}..."
exec "$@"
