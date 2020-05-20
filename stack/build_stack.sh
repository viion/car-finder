#!/usr/bin/env bash

#
# Setup
#
echo "Setting up"
cd /vagrant
USER=vagrant
sudo locale-gen en_GB.UTF-8
sudo apt-get update -y
sudo apt-get upgrade -y
sudo apt-get install -y unzip curl git dos2unix

#
# NGINX
#
echo "Installing: NGINX"
sudo add-apt-repository ppa:nginx/stable -y
sudo apt-get update
sudo apt-get install -y nginx
sudo cp /stack/stack/nginx/default /etc/nginx/sites-available/default
sudo cp /stack/stack/nginx/nginx.conf /etc/nginx/nginx.conf

#
# PHP
#
echo "Installing: PHP + Composer"
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update -y
sudo apt-get install -y php7.3-fpm php-apcu php-imagick php7.3-dev php7.3-cli php7.3-tidy php7.3-json
sudo apt-get install -y php7.3-intl php7.3-mysql php7.3-sqlite php7.3-curl php7.3-gd
sudo apt-get install -y php7.3-mbstring php7.3-dom php7.3-xml php7.3-zip php7.3-bcmath
sudo sed -i 's|display_errors = Off|display_errors = On|' /etc/php/7.3/fpm/php.ini
sudo sed -i 's|memory_limit = 128M|memory_limit = -1|' /etc/php/7.3/fpm/php.ini
sudo sed -i "s|www-data|$USER|" /etc/php/7.3/fpm/pool.d/www.conf

#
# MySQL
#
echo "Installing: MySQL"
echo "mysql-server mysql-server/root_password password tester" | debconf-set-selections
echo "mysql-server mysql-server/root_password_again password tester" | debconf-set-selections
sudo apt install mysql-server -y
mysql -uroot -ptester < ./mysql/db.sql

#
# Redis
#
echo "Installing: Redis"
sudo apt-get install redis-server -y
git clone https://github.com/phpredis/phpredis.git
cd phpredis && phpize && ./configure && make && sudo make install
cd /vagrant
rm -rf /vagrant/phpredis
sudo echo "extension=redis.so" > /etc/php/7.3/mods-available/redis.ini
sudo ln -sf /etc/php/7.3/mods-available/redis.ini /etc/php/7.3/fpm/conf.d/20-redis.ini
sudo ln -sf /etc/php/7.3/mods-available/redis.ini /etc/php/7.3/cli/conf.d/20-redis.ini
sudo service php7.3-fpm restart

#
# This is more Symfony specific...
# Create folders outside the sync folder for better performance
#
echo "Creating symfony dictories"
sudo mkdir -p /symfony
sudo chown vagrant:vagrant /symfony
sudo chmod -R 777 /symfony

#
# Build ssh keys
#
echo "Generating SSL Certificates"
openssl req -x509 -out /home/vagrant/localhost.crt -keyout /home/vagrant/localhost.key \
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")

#
# Finish
#
echo "Finishing up ..."
sudo service nginx restart
sudo service php7.3-fpm restart
sudo apt-get autoremove -y
sudo apt-get update -y
sudo apt-get upgrade -y

#
# Goodbye!
#
echo "All finished"
echo "Stick your code in /website"
echo "You can ssh in via: vagrant ssh"
echo "Enjoy!"
