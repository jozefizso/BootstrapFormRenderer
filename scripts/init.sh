#!/bin/env bash

# Install PHP 5.6
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php5.6

# Install Composer 2.2
curl --proto '=https' --tlsv1.2 -sSf https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --2.2 --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
