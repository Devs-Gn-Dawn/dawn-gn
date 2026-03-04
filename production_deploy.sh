#!/bin/bash
set -e

# Se placer à la racine du projet (répertoire du script)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Configuration
REPO_URL="https://github.com/Devs-Gn-Dawn/dawn-gn.git"
BRANCH="init_symfony"
TEMP_DIR="/tmp/deploy_temp"
APP_DIR="app"
BACKUP_DIR="/tmp/dawn_backup_$(date +%Y%m%d_%H%M%S)"

# Création du dossier temporaire
echo "Création du dossier temporaire..."
rm -rf $TEMP_DIR
mkdir -p $TEMP_DIR

# Clonage du repository
echo "Clonage du repository..."
git clone -b $BRANCH $REPO_URL $TEMP_DIR

# Création de la sauvegarde
echo "Création de la sauvegarde..."
mkdir -p $BACKUP_DIR
if [ -d "$APP_DIR/src" ]; then
    cp -r $APP_DIR/src $BACKUP_DIR/
    echo "Sauvegarde de src effectuée"
fi
if [ -d "$APP_DIR/templates" ]; then
    cp -r $APP_DIR/templates $BACKUP_DIR/
    echo "Sauvegarde de templates effectuée"
fi
if [ -d "$APP_DIR/public" ]; then
    cp -r $APP_DIR/public $BACKUP_DIR/
    echo "Sauvegarde de public effectuée"
fi
if [ -d "$APP_DIR/assets" ]; then
    cp -r $APP_DIR/assets $BACKUP_DIR/
    echo "Sauvegarde de assets effectuée"
fi
if [ -d "$APP_DIR/config" ]; then
    cp -r $APP_DIR/config $BACKUP_DIR/
    echo "Sauvegarde de config effectuée"
fi

# Demande de confirmation
echo "ATTENTION: Les dossiers suivants vont être écrasés :"
echo "- $APP_DIR/src"
echo "- $APP_DIR/templates"
echo "- $APP_DIR/public"
echo "- $APP_DIR/assets"
echo "- $APP_DIR/config"
echo "Une sauvegarde a été créée dans : $BACKUP_DIR"
read -p "Voulez-vous continuer ? (o/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Oo]$ ]]
then
    echo "Déploiement annulé"
    exit 1
fi

# Copie des dossiers nécessaires
echo "Copie des fichiers..."
cp -rv $TEMP_DIR/$APP_DIR/src $APP_DIR/
cp -rv $TEMP_DIR/$APP_DIR/templates $APP_DIR/
cp -rv $TEMP_DIR/$APP_DIR/public $APP_DIR/
cp -rv $TEMP_DIR/$APP_DIR/assets $APP_DIR/
cp -rv $TEMP_DIR/$APP_DIR/config $APP_DIR/

# Build des assets (augmenter la mémoire Node si nécessaire pour éviter "Out of memory" WebAssembly)
echo "Build des assets..."
cd $APP_DIR
export NODE_OPTIONS="${NODE_OPTIONS:---max-old-space-size=2048}"
npm run build || { echo "ATTENTION: le build npm a échoué (ex. mémoire). Vous pouvez builder en local et déployer public/build/ manuellement."; }
cd "$SCRIPT_DIR"

# Nettoyage du cache
echo "Nettoyage du cache..."
cd $APP_DIR
php bin/console cache:clear

# Suppression du dossier temporaire
echo "Suppression du dossier temporaire..."
rm -rf $TEMP_DIR

echo "Déploiement terminé avec succès!"
echo "Sauvegarde conservée dans : $BACKUP_DIR" 