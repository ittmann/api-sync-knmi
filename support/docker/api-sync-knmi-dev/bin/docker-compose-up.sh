#!/usr/bin/bash

scriptdir="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
parentdir="$(dirname "${scriptdir}")"
dockerenv="${parentdir%"${parentdir##*[!/]}"}"
dockerenv="${dockerenv##*/}"
projectdir=$(dirname $(dirname $(dirname $parentdir)))

cd $projectdir

docker network inspect api-sync-knmi-net >/dev/null 2>&1 || \
    docker network create --driver bridge api-sync-knmi-net

docker compose -f support/docker/api-sync-knmi-dev/docker-compose.yml --env-file support/docker/.env up -d
