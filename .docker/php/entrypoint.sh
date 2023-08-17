#!/bin/bash

# Set uid of host machine
usermod --non-unique --uid "${HOST_UID}" www-data
groupmod --non-unique --gid "${HOST_GID}" www-data

# Composer
if [ ! -d "vendor" ]; then
    composer install
fi

# NPM
. $NVM_DIR/nvm.sh
if [ ! -d "node_modules" ]; then
    npm install
fi
npm run watch