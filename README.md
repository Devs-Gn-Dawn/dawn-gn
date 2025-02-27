# Guide d'Installation du Projet

## Prérequis

- Docker
- Docker Compose
- Git
- Node.js (pour le développement local)
- npm (pour le développement local)

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

1. Cloner le projet

```bash
git clone [URL_DU_PROJET]
cd [NOM_DU_PROJET]
```

2. Copier les fichiers d'environnement

```bash
cp .env.dist .env
cp .env.dev.dist .env.dev
cp .env.test.dist .env.test
```

3. Construire et démarrer les conteneurs Docker

```bash
make up
```

4. Installer les dépendances

```bash
make install
```

## Commandes Make Disponibles

- `make up` : Démarre les conteneurs Docker
- `make down` : Arrête les conteneurs Docker
- `make install` : Installe toutes les dépendances (Composer + npm)
- `make build` : Compile les assets avec npm
- `make watch` : Lance le watcher pour le développement
- `make tests` : Lance les tests
- `make bash` : Ouvre un terminal dans le conteneur PHP

## Accès à l'Application

- Application : http://localhost:8080
- Adminer (gestionnaire de base de données) : http://localhost:8081

## Commandes Utiles

- Arrêter les conteneurs : `docker-compose down`
- Voir les logs : `docker-compose logs -f`
- Accéder au conteneur PHP : `docker-compose exec php bash`
- Lancer les tests : `docker-compose exec php bin/phpunit`

## Structure du Projet

Le projet suit la structure standard d'une application Symfony :

```
├── app/                # Application Symfony
│   ├── assets/        # Fichiers source JS et CSS
│   ├── bin/           # Exécutables
│   ├── config/        # Configuration
│   ├── public/        # Fichiers publics
│   ├── src/           # Code source PHP
│   ├── templates/     # Templates Twig
│   ├── translations/  # Fichiers de traduction
│   ├── var/          # Fichiers temporaires
│   └── vendor/       # Dépendances PHP
├── docker/           # Configuration Docker
├── .env             # Variables d'environnement
├── .env.dev         # Variables pour le développement
├── .env.test        # Variables pour les tests
├── docker-compose.yml
├── Makefile
└── install-symfony.sh
```

## Contribution

1. Créer une nouvelle branche pour votre fonctionnalité
2. Commiter vos changements
3. Créer une Pull Request

## Support

Pour toute question ou problème, veuillez ouvrir une issue dans le gestionnaire de tickets.
