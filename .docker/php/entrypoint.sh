#!/bin/bash

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