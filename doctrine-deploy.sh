#!/bin/bash

# drop schema with option --drop
if [[ $1 = '--drop' ]]
then
    php bin/console doctrine:schema:drop --force
fi

# create the admin user
php bin/console app:create-admin admin@coloc-matching.fr Secret1234

# create and populate the app schema
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load -n