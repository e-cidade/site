ARG version_php

FROM php:${version_php}
ARG version_php
ARG version_node
ARG host_uid
ARG host_gid
ENV VERSION_NODE=${version_node}

RUN apt-get update

RUN apt-get install -y \
    git \
    unzip

RUN usermod --non-unique --uid ${host_uid} www-data \
    && groupmod --non-unique --gid ${host_gid} www-data

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions \
    xdebug \
    zip \
    @composer \
    && rm /usr/local/bin/install-php-extensions

# Install node
ENV NVM_DIR=/opt/nvm
RUN git clone https://github.com/nvm-sh/nvm.git $NVM_DIR
RUN cd $NVM_DIR && \
    git checkout `git describe --abbrev=0 --tags --match "v[0-9]*" $(git rev-list --tags --max-count=1)` && \
    . $NVM_DIR/nvm.sh && \
    nvm install ${VERSION_NODE} && \
    nvm use node && \
    npm install --global npm@8 && \
    npm install --global webpack && \
    npm install --global webpack-cli
RUN echo "source ${NVM_DIR}/nvm.sh" > $HOME/.bashrc

WORKDIR /jigsaw

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh"]
