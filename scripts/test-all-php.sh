#!/bin/sh

VERSIONS="8.3 8.4 8.5"
CONFIG=".ddev/config.yaml"
EXIT_CODE=0

set_php_version() {
  sed -i '' "s/^php_version: .*/php_version: '$1'/" "$CONFIG"
}

restore() {
  echo "\nRestoring PHP 8.3"
  set_php_version "8.3"
  ddev restart
  ddev composer update
}

trap restore EXIT

for v in $VERSIONS; do
  echo "\n--- PHP $v ---"
  set_php_version "$v"
  ddev restart
  ddev composer update
  ddev import-db --file=./tests/fixtures/test-db.sql.gz
  ddev exec ./vendor/bin/pest
  if [ $? -ne 0 ]; then
    echo "\nTests failed on PHP $v"
    EXIT_CODE=1
    break
  fi
done

exit $EXIT_CODE
