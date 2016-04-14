#!/usr/bin/env bash

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=512m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

sudo apt-get update
sudo apt-get install -y postgresql php5-gd php5 php5-curl php5-pgsql git php5-intl php5-geoip curl zip  phpunit

mkdir /home/vagrant/bin
cd /home/vagrant/bin
wget https://getcomposer.org/composer.phar

cd /vagrant
php /home/vagrant/bin/composer.phar  install

mkdir /home/vagrant/fileStore
chown vagrant:users  /home/vagrant/fileStore

mkdir /home/vagrant/logs
chown vagrant:users  /home/vagrant/logs

su --login -c "psql -c \"CREATE USER openacalendar WITH PASSWORD 'password';\"" postgres
su --login -c "psql -c \"CREATE DATABASE openacalendar WITH OWNER openacalendar ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

cp /vagrant/vagrant/tests/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/tests/test /home/vagrant/test
chmod a+rx /home/vagrant/test

