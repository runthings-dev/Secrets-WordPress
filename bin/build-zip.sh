#!/bin/bash

# Plugin slug, directory name of the plugin
PLUGINSLUG="runthings-secrets"

# Create the build directory if it doesn't exist
mkdir -p build

# Create a zip file excluding the files and directories specified in .distignore
zip -r build/${PLUGINSLUG}.zip . -x@.distignore
