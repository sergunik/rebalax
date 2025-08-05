#!/bin/sh
chown -R www-data:www-data /var/www
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
