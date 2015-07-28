#!/bin/bash
php composer.phar self-update
php composer.phar update
echo "Starting web server..."
apache2-foreground