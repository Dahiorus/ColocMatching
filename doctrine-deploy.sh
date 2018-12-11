#!/bin/bash

# drop schema with option --drop
if [[ $1 = '--drop' ]]; then
    php bin/console doctrine:schema:drop --force
fi

# create the app schema
php bin/console doctrine:schema:create

# populate de database
php bin/console app:create-admin admin@coloc-matching.fr Secret1234 --enabled
php bin/console doctrine:fixtures:load -n --append
