#!/bin/sh
set -e

# Définir les permissions sur le répertoire var
mkdir -p /var/www/app/var
chown -R www-data:www-data /var/www/app/var
chmod -R 775 /var/www/app/var

# Démarrer Apache en arrière-plan
apache2-foreground 