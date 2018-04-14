#!/usr/bin/env bash

set -e

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

sudo apt-get update
sudo apt-get install -y postgresql apache2 php-mbstring php-gd php php-curl php-pgsql git php-intl php-geoip curl zip  phpunit libapache2-mod-php beanstalkd

mkdir /fileStore
chown www-data:www-data  /fileStore

mkdir /logs
chown www-data:www-data  /logs

mkdir -p /bin
wget -O /bin/composer.phar -q https://getcomposer.org/composer.phar
wget -O /bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64
chmod a+x /bin/mailhog

cd /vagrant
php /bin/composer.phar install

cp /vagrant/vagrant/app/apache.conf /etc/apache2/sites-enabled/
cp /vagrant/vagrant/app/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/app/config.php /vagrant/config.php
cp /vagrant/vagrant/app/99-custom.ini /etc/php/7.0/apache2/conf.d/
cp /vagrant/vagrant/app/test /home/ubuntu/test
chmod a+rx /home/ubuntu/test

sudo su --login -c "psql -c \"CREATE USER openacalendartest WITH PASSWORD 'testpassword';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE openacalendartest WITH OWNER openacalendartest ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

sudo su --login -c "psql -c \"CREATE USER openacalendar WITH PASSWORD 'password';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE openacalendar WITH OWNER openacalendar ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres


if [ -f /vagrant/import.sql ]
then
    export PGPASSWORD=password
    psql -U openacalendar -hlocalhost  openacalendar -f /vagrant/import.sql
fi

php /vagrant/core/cli/upgradeDatabase.php
php /vagrant/core/cli/loadStaticData.php

chown www-data:www-data  /vagrant/cache/templates.web


a2enmod rewrite
/etc/init.d/apache2 restart

echo "alias db='psql -U openacalendar openacalendar -hlocalhost'" >> /home/ubuntu/.bashrc
echo "localhost:5432:openacalendar:openacalendar:password" > /home/ubuntu/.pgpass
chown ubuntu:ubuntu /home/ubuntu/.pgpass
chmod 0600 /home/ubuntu/.pgpass

echo "cd /vagrant" >> /home/ubuntu/.bashrc
echo "alias test='/home/ubuntu/test'" >> /home/ubuntu/.bashrc
echo "alias composer-update='cd /vagrant && php /bin/composer.phar update'" >> /home/ubuntu/.bashrc
echo "alias composer-install='cd /vagrant && php /bin/composer.phar install'" >> /home/ubuntu/.bashrc
