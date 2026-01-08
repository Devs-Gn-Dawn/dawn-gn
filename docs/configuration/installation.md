# Guide d'installation

Ce guide vous accompagne dans l'installation de l'application Dawn GN.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **Docker** : Version 20.10 ou supérieure
- **Docker Compose** : Version 2.0 ou supérieure
- **Git** : Pour cloner le dépôt
- **Node.js** : Version 18 ou supérieure (pour le développement local)
- **npm** : Version 9 ou supérieure (pour le développement local)

### Vérification des prérequis

```bash
docker --version
docker compose version
git --version
node --version
npm --version
```

## Installation

### Option 1 : Installation Automatique (Recommandée)

Utilisez le script d'installation automatique qui configurera l'environnement pour vous :

```bash
chmod +x install-symfony.sh
./install-symfony.sh
```

Ce script va :
- Créer et configurer les fichiers `.env`
- Générer les secrets de sécurité
- Configurer la base de données
- Installer les dépendances Composer
- Installer les dépendances npm
- Configurer les permissions
- Préparer l'environnement de développement

### Option 2 : Installation Manuelle

Si vous préférez installer manuellement, suivez ces étapes :

#### 1. Cloner le projet

```bash
git clone [URL_DU_PROJET]
cd dawn-gn
```

#### 2. Copier les fichiers d'environnement

```bash
cp .env.dist .env
cp .env.dev.dist .env.dev
cp .env.test.dist .env.test
```

#### 3. Configurer les variables d'environnement

Éditez le fichier `.env` et configurez :

```env
APP_SECRET=change_this_secret  # Générez un secret unique
DATABASE_URL="mysql://db_user:db_password@db:3306/db_name?serverVersion=8.0"
MYSQL_ROOT_PASSWORD=change_this_password
MYSQL_DATABASE=dawn_gn
MYSQL_USER=dawn_user
MYSQL_PASSWORD=change_this_password
```

**Important** : Remplacez tous les `change_this_*` par des valeurs sécurisées.

#### 4. Générer le secret de l'application

```bash
cd app
php bin/console secrets:generate-keys
```

#### 5. Construire et démarrer les conteneurs Docker

```bash
make up
```

Cette commande va :
- Construire l'image Docker PHP
- Démarrer les conteneurs (web, db, adminer)
- Créer le réseau Docker
- Créer le volume MySQL

#### 6. Installer les dépendances

```bash
make install
```

Cette commande installe :
- Les dépendances PHP via Composer
- Les dépendances JavaScript/CSS via npm

#### 7. Exécuter les migrations

```bash
make migrate
```

Cette commande crée les tables de la base de données.

## Vérification de l'installation

### Accéder à l'application

- **Application** : http://localhost:8080
- **Adminer** (gestionnaire de base de données) : http://localhost:8081

### Vérifier les conteneurs

```bash
docker compose ps
```

Vous devriez voir trois conteneurs en cours d'exécution :
- `dawn-gn-web-1` (PHP/Apache)
- `dawn-gn-db-1` (MySQL)
- `dawn-gn-adminer-1` (Adminer)

### Vérifier les logs

```bash
docker compose logs -f
```

## Commandes Make disponibles

- `make up` : Démarre les conteneurs Docker
- `make down` : Arrête les conteneurs Docker
- `make install` : Installe toutes les dépendances (Composer + npm)
- `make build` : Compile les assets avec npm pour la production
- `make dev` : Lance le build en mode développement
- `make watch` : Lance le build en mode watch (recompilation automatique)
- `make migrate` : Exécute les migrations Doctrine
- `make migration-status` : Affiche le statut des migrations
- `make bash` : Ouvre un terminal dans le conteneur PHP
- `make clean` : Nettoie les dépendances et le cache

## Commandes Docker utiles

### Arrêter les conteneurs

```bash
docker compose down
```

### Voir les logs

```bash
docker compose logs -f
```

### Accéder au conteneur PHP

```bash
docker compose exec web bash
```

Ou avec Make :

```bash
make bash
```

### Redémarrer les conteneurs

```bash
docker compose restart
```

### Reconstruire les conteneurs

```bash
docker compose up -d --build
```

## Configuration de la base de données

### Connexion avec Adminer

1. Accédez à http://localhost:8081
2. Utilisez les identifiants :
   - **Système** : MySQL
   - **Serveur** : db
   - **Utilisateur** : root (ou MYSQL_USER)
   - **Mot de passe** : MYSQL_ROOT_PASSWORD
   - **Base de données** : MYSQL_DATABASE

### Connexion depuis le conteneur

```bash
docker compose exec db mysql -u root -p
```

## Développement

### Mode watch pour les assets

```bash
make watch
```

Cette commande surveille les modifications des fichiers JavaScript/CSS et les recompile automatiquement.

### Accès au shell Symfony

```bash
docker compose exec web bin/console
```

### Exécuter les tests

```bash
docker compose exec web bin/phpunit
```

## Structure du projet

Le projet suit la structure standard d'une application Symfony :

```
├── app/                # Application Symfony
│   ├── assets/        # Fichiers source JS et CSS
│   ├── bin/           # Exécutables
│   ├── config/        # Configuration
│   ├── migrations/    # Migrations Doctrine
│   ├── public/        # Point d'entrée public
│   ├── src/           # Code source PHP
│   ├── templates/     # Templates Twig
│   ├── translations/  # Fichiers de traduction
│   ├── var/          # Fichiers temporaires
│   └── vendor/       # Dépendances PHP
├── docker/           # Configuration Docker
├── docs/             # Documentation
├── .env             # Variables d'environnement
├── docker-compose.yml
├── Makefile
└── README.md
```

## Problèmes courants

### Les conteneurs ne démarrent pas

1. Vérifiez que Docker est en cours d'exécution
2. Vérifiez que les ports 8080, 8081 et 3306 ne sont pas utilisés
3. Consultez les logs : `docker compose logs`

### Erreur de connexion à la base de données

1. Vérifiez que le conteneur `db` est démarré
2. Vérifiez la variable `DATABASE_URL` dans `.env`
3. Attendez quelques secondes que MySQL soit complètement démarré

### Erreur de permissions

```bash
sudo chown -R $USER:$USER app/var
chmod -R 755 app/var
```

### Le cache ne se vide pas

```bash
docker compose exec web bin/console cache:clear
```

## Prochaines étapes

Une fois l'installation terminée :

1. Consultez la [documentation de configuration](configuration.md)
2. Créez votre premier utilisateur administrateur
3. Explorez les [guides d'utilisation](../guides/guide-administrateur.md)

## Navigation

- [Configuration](configuration.md)
- [Dépannage](depannage.md)
- [Documentation principale](../README.md)
