#!/bin/bash
set -e

# Se placer à la racine du projet (répertoire du script)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Configuration
APP_DIR="app"
BACKUP_BASE="/tmp/dawn_backup_"

# Trouver la dernière sauvegarde
echo "Recherche de la dernière sauvegarde..."
LATEST_BACKUP=$(ls -td ${BACKUP_BASE}* 2>/dev/null | head -1)

if [ -z "$LATEST_BACKUP" ]; then
    echo "Aucune sauvegarde trouvée !"
    exit 1
fi

echo "Dernière sauvegarde trouvée : $LATEST_BACKUP"

# Demande de confirmation
echo "ATTENTION: Les dossiers suivants vont être restaurés depuis la sauvegarde :"
echo "- $APP_DIR/src"
echo "- $APP_DIR/templates"
echo "- $APP_DIR/public"
echo "- $APP_DIR/assets"
echo "- $APP_DIR/config"
read -p "Voulez-vous continuer ? (o/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Oo]$ ]]
then
    echo "Rollback annulé"
    exit 1
fi

# Restauration des dossiers
echo "Restauration des fichiers..."
if [ -d "$LATEST_BACKUP/src" ]; then
    rm -rf $APP_DIR/src
    cp -rv $LATEST_BACKUP/src $APP_DIR/
    echo "Restauration de src effectuée"
fi

if [ -d "$LATEST_BACKUP/templates" ]; then
    rm -rf $APP_DIR/templates
    cp -rv $LATEST_BACKUP/templates $APP_DIR/
    echo "Restauration de templates effectuée"
fi

if [ -d "$LATEST_BACKUP/public" ]; then
    rm -rf $APP_DIR/public
    cp -rv $LATEST_BACKUP/public $APP_DIR/
    echo "Restauration de public effectuée"
fi

if [ -d "$LATEST_BACKUP/assets" ]; then
    rm -rf $APP_DIR/assets
    cp -rv $LATEST_BACKUP/assets $APP_DIR/
    echo "Restauration de assets effectuée"
fi

if [ -d "$LATEST_BACKUP/config" ]; then
    rm -rf $APP_DIR/config
    cp -rv $LATEST_BACKUP/config $APP_DIR/
    echo "Restauration de config effectuée"
fi

# Nettoyage du cache
echo "Nettoyage du cache..."
cd $APP_DIR
php bin/console cache:clear

echo "Rollback terminé avec succès!"
echo "Les fichiers ont été restaurés depuis : $LATEST_BACKUP" 