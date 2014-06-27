#!/bin/bash

cd workspace

echo "Cloning RoutingAutoBundle"
echo "-------------------------"
echo ""

if [ ! -e workspace ];
then
    mkdir workspace
fi

git clone git@github.com:symfony-cmf/RoutingAutoBundle
cd RoutingAutoBundle

echo "Installing dependencies"
echo "-----------------------"
echo ""

composer require symfony-cmf/RoutingAuto dev-master --no-update
composer require symfony/framework-bundle:${SYMFONY_VERSION} --no-update
composer install --dev --prefer-dist

echo "Initializing Jackalope Doctrine-DBAL"
echo "------------------------------------"
echo ""

vendor/symfony-cmf/testing/bin/travis/phpcr_odm_doctrine_dbal.sh

echo "Running tests"
echo "-------------"
echo ""

phpunit

cd -
