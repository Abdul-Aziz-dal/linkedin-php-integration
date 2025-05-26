#!/bin/bash
set -e

echo "Navigating to /var/www/html"
cd /var/www/html

echo "Setting permissions to 755 recursively"
chmod -R 755 .

echo "Install script completed successfully."