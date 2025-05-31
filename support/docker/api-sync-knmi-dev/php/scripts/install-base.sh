#!/bin/sh

wget --no-verbose -O /usr/local/bin/local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v2.0.6/local-php-security-checker_2.0.6_linux_amd64
#sha256sum /usr/local/bin/local-php-security-checker
[ "314309702970bd8f2eed68301c3c42012a938fb8ae5c977c4ab0db57bb69b23c  /usr/local/bin/local-php-security-checker" = "$(sha256sum /usr/local/bin/local-php-security-checker)" ] || { echo >&2 "local-php-security-checker checksum failed!"; exit 1; }
chmod +x /usr/local/bin/local-php-security-checker
