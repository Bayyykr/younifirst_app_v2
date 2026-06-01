#!/usr/bin/env sh
set -e

: "${PORT:=80}"

rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
a2enmod mpm_prefork >/dev/null

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
    mkdir -p storage/firebase
    printf '%s' "$FIREBASE_CREDENTIALS_JSON" > storage/firebase/service-account.json
    export FIREBASE_CREDENTIALS="storage/firebase/service-account.json"
fi

php artisan storage:link --force || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

php artisan config:cache
php artisan view:cache

exec apache2-foreground
