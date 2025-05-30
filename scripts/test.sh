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

echo "Container is alive. Preparing test environment..."

echo "Running tests..."
# Run the tests
ddev exec ./vendor/bin/pest
