#!/bin/bash

# Get the absolute path to the current directory
PLUGIN_DIR=$(pwd)
PLUGINSLUG="runthings-secrets"

# Create the build directory if it doesn't exist
mkdir -p ${PLUGIN_DIR}/build

# Create a temporary directory to stage the files to be zipped
TEMP_DIR=$(mktemp -d)

# Copy all files to the temporary directory, excluding the patterns in .distignore
rsync -av --exclude-from=${PLUGIN_DIR}/.distignore ${PLUGIN_DIR}/ ${TEMP_DIR}/

# Create the zip file from the temporary directory
cd ${TEMP_DIR}
zip -r ${PLUGIN_DIR}/build/${PLUGINSLUG}.zip .

# Clean up the temporary directory
rm -rf ${TEMP_DIR}
