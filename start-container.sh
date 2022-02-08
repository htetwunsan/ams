#!/usr/bin/env bash

ROLE=${ROLE:-app}

echo "App running on $APPENV environment."

if [ $ROLE = "app" ]; then
    echo "PHP-FPM started."
    exec /usr/sbin/php-fpm8.1 -F
else
    echo "Unknown Role."
    exit 1
fi
