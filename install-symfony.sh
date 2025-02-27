#!/bin/bash

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}Installation de Symfony...${NC}"

# Vérification et création des fichiers .env à la racine
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}Création du fichier .env à partir de .env.dist...${NC}"
    cp .env.dist .env
    
    # Génération d'un nouveau APP_SECRET
    NEW_SECRET=$(openssl rand -hex 16)
    sed -i "s/change_this_secret/$NEW_SECRET/" .env
    
    # Génération de mots de passe aléatoires pour la base de données
    DB_ROOT_PASSWORD=$(openssl rand -hex 12)
    DB_PASSWORD=$(openssl rand -hex 12)
    
    # Configuration des valeurs par défaut
    sed -i "s/<dbname>/app_db/" .env
    sed -i "s/<user>/app_user/" .env
    sed -i "s/<password>/$DB_PASSWORD/" .env
    sed -i "s/change_this_password/$DB_ROOT_PASSWORD/" .env
    
    # Mise à jour de DATABASE_URL avec les nouvelles valeurs
    sed -i "s/db_user:db_password/app_user:$DB_PASSWORD/" .env
fi

# Création de la structure du projet
echo -e "${YELLOW}Création de la structure du projet...${NC}"
mkdir -p app/{assets,bin,config,migrations,public,src,templates,translations,var}

# Installation des dépendances Composer si composer.json existe
if [ -f "app/composer.json" ]; then
    echo -e "${YELLOW}Installation des dépendances Composer...${NC}"
    cd app && composer install && cd ..
fi

# Installation des dépendances npm si package.json existe
if [ -f "app/package.json" ]; then
    echo -e "${YELLOW}Installation des dépendances npm...${NC}"
    cd app && npm install && cd ..
fi

# Configuration des permissions
echo -e "${YELLOW}Configuration des permissions...${NC}"
chmod -R 777 app/var

echo -e "${GREEN}Installation terminée !${NC}"
echo -e "${YELLOW}Vous pouvez maintenant :"
echo -e "1. Vérifier et personnaliser les valeurs dans le fichier .env"
echo -e "2. Lancer 'make install' pour installer toutes les dépendances"
echo -e "3. Lancer 'make dev' pour démarrer le serveur de développement${NC}" 