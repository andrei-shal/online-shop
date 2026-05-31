#!/bin/sh
set -e

printf "%s\n" \
"defaults" \
"auth on" \
"tls on" \
"tls_trust_file /etc/ssl/certs/ca-certificates.crt" \
"" \
"account gmail" \
"host smtp.gmail.com" \
"port 587" \
"from ${MAIL_USER}" \
"user ${MAIL_USER}" \
"password ${MAIL_PASS}" \
"" \
"account default : gmail" \
> /etc/msmtprc

chown www-data:www-data /etc/msmtprc
chmod 600 /etc/msmtprc

php /var/www/html/init.php

exec "$@"