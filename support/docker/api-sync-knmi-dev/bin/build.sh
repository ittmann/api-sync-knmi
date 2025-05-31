#!/usr/bin/bash

scriptdir="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
parentdir="$(dirname "${scriptdir}")"
dockerenv="${parentdir%"${parentdir##*[!/]}"}"
dockerenv="${dockerenv##*/}"
projectdir=$(dirname $(dirname $(dirname $parentdir)))

cd $projectdir

docker build -t knmi-api:latest ${parentdir}/php/
