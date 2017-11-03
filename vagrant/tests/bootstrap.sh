#!/usr/bin/env bash

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=1536m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

sudo apt-get update
sudo apt-get install -y postgresql php-gd php php-curl php-pgsql git php-intl php-geoip curl zip  phpunit

mkdir /home/ubuntu/bin
cd /home/ubuntu/bin
wget -q https://getcomposer.org/composer.phar

cd /vagrant
php /home/ubuntu/bin/composer.phar  install

mkdir /fileStore
chown vagrant:users  /fileStore

mkdir /logs
chown vagrant:users  /logs


cp /vagrant/vagrant/tests/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/tests/test /home/ubuntu/test
chmod a+rx /home/ubuntu/test

