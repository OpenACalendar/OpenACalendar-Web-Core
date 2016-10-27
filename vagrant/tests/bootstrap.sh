#!/usr/bin/env bash

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=1536m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

sudo apt-get update
sudo apt-get install -y postgresql php-gd php php-curl php-pgsql git php-intl php-geoip curl zip  phpunit

mkdir /home/vagrant/bin
cd /home/vagrant/bin
wget -q https://getcomposer.org/composer.phar

cd /vagrant
php /home/vagrant/bin/composer.phar  install

mkdir /home/vagrant/fileStore
chown vagrant:users  /home/vagrant/fileStore

mkdir /home/vagrant/logs
chown vagrant:users  /home/vagrant/logs


cp /vagrant/vagrant/tests/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/tests/test /home/vagrant/test
chmod a+rx /home/vagrant/test

