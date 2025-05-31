#!/bin/sh

# Check if the container is running by attempting to execute a command
if ! ddev exec true 2>/dev/null; then
  echo "Container is not running. Starting it now..."
  ddev start
fi

# Wait for the container to be alive
echo "Waiting for the container to be alive..."
while ! ddev exec true 2>/dev/null; do
  sleep 2
done

# Prepare test environment
echo "Container is alive. Preparing environment..."
ddev composer install
ddev import-db --file=./tests/fixtures/test-db.sql.gz

# Run the tests
echo "Running tests..."
ddev exec ./vendor/bin/pest
