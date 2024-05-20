#!/bin/bash

PLUGIN_DIR=$(pwd)
PLUGINSLUG="runthings-secrets"

# Check if the script is being run from the bin directory
if [[ $(basename "$PLUGIN_DIR") == "bin" ]]; then
  echo "Error: This script should not be run from the bin directory."
  echo "Usage: Run the script from the plugin's root directory."
  echo "Example: ./bin/build-zip.sh"
  exit 1
fi

# Run makepot.sh to generate/update translation files
echo "Running makepot.sh to generate/update translation files..."
if ! ./bin/makepot.sh; then
  echo "Error: makepot.sh failed."
  exit 1
fi

# Create the build directory if it doesn't exist
echo "Creating build directory..."
mkdir -p ${PLUGIN_DIR}/build

# Create a temporary directory to stage the files to be zipped
TEMP_DIR=$(mktemp -d)
echo "Created temporary directory at ${TEMP_DIR}"

# Copy all files to the temporary directory, excluding the patterns in .distignore
echo "Copying files to temporary directory, excluding patterns in .distignore..."
if ! rsync -av --exclude-from=${PLUGIN_DIR}/.distignore ${PLUGIN_DIR}/ ${TEMP_DIR}/; then
  echo "Error: rsync failed."
  rm -rf ${TEMP_DIR}
  exit 1
fi

# Create the zip file from the temporary directory
cd ${TEMP_DIR}
echo "Creating zip file..."
if ! zip -r ${PLUGIN_DIR}/build/${PLUGINSLUG}.zip .; then
  echo "Error: zip failed."
  cd ${PLUGIN_DIR}
  rm -rf ${TEMP_DIR}
  exit 1
fi
echo "Zip file created at ./build/${PLUGINSLUG}.zip"

# Clean up the temporary directory
cd ${PLUGIN_DIR}
echo "Cleaning up temporary directory..."
rm -rf ${TEMP_DIR}
echo "Temporary directory cleaned up."

echo "Build completed successfully."
