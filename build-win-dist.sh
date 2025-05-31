#!/bin/sh

echo "Creating dist folder..."
mkdir "dist"

echo "Extracting archive artifact ..."
unzip ${CI_PROJECT_NAME}-${CI_COMMIT_SHORT_SHA}.zip -d dist

echo "Downloading PHP..."
curl -sSLf -o php.zip https://windows.php.net/downloads/releases/latest/php-8.3-Win32-vs16-x64-latest.zip || exit 1

echo "Extracting PHP to dist folder..."
unzip php.zip -d dist/PHP

echo "Downloading https://curl.se/ca/cacert.pem..."
curl -sSLf -o dist/PHP/cacert.pem https://curl.se/ca/cacert.pem || exit 1

echo "Install SQLSRV extension..."
curl -sSLf -o SQLSRV.zip https://github.com/microsoft/msphpsql/releases/download/v5.12.0/Windows_5.12.0RTW.zip || exit 1
unzip SQLSRV.zip
cp Windows/php_sqlsrv_83_ts_x64.dll dist/PHP/ext
cp Windows/php_pdo_sqlsrv_83_ts_x64.dll dist/PHP/ext

echo "Copy php.ini-development to php.ini..."
cp dist/PHP/php.ini-development dist/PHP/php.ini

cat <<EOT >> dist/PHP/php.ini
extension_dir="ext"
extension=curl
extension=openssl
extension=intl
curl.cainfo=".\PHP\cacert.pem"
openssl.cafile=".\PHP\cacert.pem"
extension=php_sqlsrv_83_ts_x64.dll
extension=php_pdo_sqlsrv_83_ts_x64.dll
date.timezone=Europe/Amsterdam
EOT

mkdir -p dist/var/tmp

sed -i 's/memory_limit = 128M/memory_limit = 8192M/' dist/PHP/php.ini

echo "${CI_COMMIT_TAG} (${CI_COMMIT_SHORT_SHA})" > dist/version

echo "Remove some redundant files from dist folder..."
rm -Rf dist/.composer
rm -Rf dist/support
rm -Rf dist/.gitlab-ci.yml
rm -Rf dist/build-win-dist.sh

echo "Cleaning up..."
rm php.zip
rm SQLSRV.zip
rm -Rf Windows
rm ${CI_PROJECT_NAME}-${CI_COMMIT_SHORT_SHA}.zip

echo "Creating distribution ZIP file..."
cd dist
zip -r ../${CI_PROJECT_NAME}-${CI_COMMIT_TAG}-dist.zip .
