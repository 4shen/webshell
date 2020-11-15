#!/bin/sh

# return true if specified directory is empty
directory_empty() {
    [ -z "$(ls -A "$1/")" ]
}

# wait for the database to start
waitfordb() {
    HOST=${DB_HOST:-mysql}
    PORT=${DB_PORT:-3306}
    echo "Connecting to ${HOST}:${PORT}"

    attempts=0
    max_attempts=30
    while [ $attempts -lt $max_attempts ]; do
        nc -z "${HOST}" "${PORT}" && break
        echo "Waiting for ${HOST}:${PORT}..."
        sleep 1
        let "attempts=attempts+1"
    done

    if [ $attempts -eq $max_attempts ]; then
        echo "Unable to contact your database at ${HOST}:${PORT}"
        exit 1
    fi

    echo "Waiting for database to settle..."
    sleep 3
}

if expr "$1" : "apache" 1>/dev/null || [ "$1" = "php-fpm7" ]; then

    MONICADIR=/var/www/monica
    ARTISAN="php ${MONICADIR}/artisan"

    # Ensure storage directories are present
    STORAGE=${MONICADIR}/storage
    mkdir -p ${STORAGE}/logs
    mkdir -p ${STORAGE}/app/public
    mkdir -p ${STORAGE}/framework/views
    mkdir -p ${STORAGE}/framework/cache
    mkdir -p ${STORAGE}/framework/sessions
    chown -R monica:apache ${STORAGE}
    chmod -R g+rw ${STORAGE}

    if [ -z "${APP_KEY:-}" -o "$APP_KEY" = "ChangeMeBy32KeyLengthOrGenerated" ]; then
        ${ARTISAN} key:generate --no-interaction
    else
        echo "APP_KEY already set"
    fi

    # Run migrations
    waitfordb
    ${ARTISAN} monica:update --force -vv

    if [ -n "${SENTRY_SUPPORT:-}" -a "$SENTRY_SUPPORT" = "true" -a -z "${SENTRY_NORELEASE:-}" -a -n "${SENTRY_ENV:-}" ]; then
        commit=$(cat .sentry-commit)
        release=$(cat .sentry-release)
        ${ARTISAN} sentry:release --release="$release" --commit="$commit" --environment="$SENTRY_ENV" --force -v || true
    fi

    # Run cron
    if [ -f "/usr/sbin/crond" ]; then
        if [ "$CRON_LEGACY" = "true" ]; then
            crond -b -l 0
        else
            echo "cron is not launched by default. Add CRON_LEGACY=true, use another container, or use supervisor."
        fi
    fi

fi

exec $@
