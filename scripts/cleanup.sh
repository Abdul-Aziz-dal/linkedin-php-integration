#!/bin/bash
set -e

echo "Cleaning /var/www/html before deployment"
rm -rf /var/www/html/*
