#!/usr/bin/bash

scriptdir="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
parentdir="$(dirname "${scriptdir}")"
dockerenv="${parentdir%"${parentdir##*[!/]}"}"
dockerenv="${dockerenv##*/}"
projectdir=$(dirname $(dirname $(dirname $parentdir)))

cd $projectdir

docker network inspect api-sync-knmi-net >/dev/null 2>&1 || \
    docker network create --driver bridge api-sync-knmi-net

docker run --rm --name knmi-api -it \
-v ${projectdir}:/application \
-v ${parentdir}/php/php-ini-overrides.ini:/usr/local/etc/php/conf.d/99-overrides.ini \
-v ${parentdir}/php/.bashrc:/home/www-data/.bashrc \
-v ~/.gitconfig:/home/www-data/.gitconfig \
-v ~/.composer:/home/www-data/.composer \
-v ~/.ssh:/home/www-data/.ssh:ro \
-v /tmp:/tmp \
-e "XDEBUG_CONFIG=client_port=9003 client_host=$XDEBUG_CLIENT_HOST" \
-e "PHP_IDE_CONFIG=serverName=knmi-api-dev" \
--network=name=api-sync-knmi-net,\"driver-opt=com.docker.network.driver.mtu=1400\" \
--user www-data \
--add-host host.docker.internal:host-gateway \
knmi-api \
bash
