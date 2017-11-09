#!/usr/bin/env bash

# This box tries to run apt itself automatically, which causes problems for our install scripts. Stop that.
systemctl disable apt-daily.service
systemctl disable apt-daily.timer
killall apt.systemd.daily

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

echo "127.0.0.1    openadevcalendar.co.uk" >> /etc/hosts
echo "127.0.0.1    test1.openadevcalendar.co.uk" >> /etc/hosts
echo "127.0.0.1    test2.openadevcalendar.co.uk" >> /etc/hosts
echo "127.0.0.1    test3.openadevcalendar.co.uk" >> /etc/hosts
echo "127.0.0.1    test4.openadevcalendar.co.uk" >> /etc/hosts

locale-gen

echo "tmpfs  /var/lib/postgresql  tmpfs  size=1024m,auto  0  0" >> /etc/fstab
mkdir /var/lib/postgresql
mount /var/lib/postgresql

apt-get update
apt-get install -y postgresql apache2 php-gd php php-curl php-pgsql git php-intl php-geoip curl zip  phpunit openjdk-8-jre-headless  php-zip libapache2-mod-php
apt-get install -y --no-install-recommends ubuntu-desktop
apt-get install -y firefox

mkdir /bin
wget -O /bin/composer.phar -q https://getcomposer.org/composer.phar
wget -O /bin/selenium.jar -q http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.0.jar

mkdir /fileStore
chown www-data:www-data  /fileStore

mkdir /logs
chown www-data:www-data  /logs

cd /vagrant
php /bin/composer.phar  install

cp /vagrant/vagrant/frontendtests/apache.conf /etc/apache2/sites-enabled/
cp /vagrant/vagrant/frontendtests/config.test.php /vagrant/config.test.php
cp /vagrant/vagrant/frontendtests/config.php /vagrant/config.php
cp /vagrant/vagrant/frontendtests/99-custom.ini /etc/php/7.0/fpm/conf.d/
cp /vagrant/vagrant/frontendtests/test /home/vagrant/test
chmod a+rx /home/vagrant/test

chown www-data:www-data  /vagrant/cache/templates.web

a2enmod rewrite
/etc/init.d/apache2 restart

cp /vagrant/vagrant/frontendtests/run /home/ubuntu/run
chmod a+x /home/ubuntu/run

cp /vagrant/vagrant/frontendtests/test /home/ubuntu/test
chmod a+x /home/ubuntu/test

# gsettings set org.gnome.desktop.session idle-delay 0

