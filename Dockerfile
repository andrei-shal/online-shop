FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y msmtp msmtp-mta ca-certificates \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN echo 'sendmail_path = "/usr/bin/msmtp -C /etc/msmtprc -t"' > /usr/local/etc/php/conf.d/sendmail.ini

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf
RUN a2enmod rewrite

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]