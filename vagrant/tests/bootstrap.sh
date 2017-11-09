#!/usr/bin/env bash

set -e

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=1536m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

sudo apt-get update
sudo apt-get install -y postgresql php-gd php php-curl php-pgsql git php-intl php-geoip curl zip  phpunit

mkdir -p /bin
wget -O /bin/composer.phar -q https://getcomposer.org/composer.phar

cd /vagrant
php /bin/composer.phar  install

mkdir /fileStore
chown www-data:www-data  /fileStore

mkdir /logs
chown www-data:www-data  /logs


cp /vagrant/vagrant/tests/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/tests/test /home/ubuntu/test
chmod a+rx /home/ubuntu/test

