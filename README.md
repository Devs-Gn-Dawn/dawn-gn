# Guide d'Installation du Projet

## Prérequis

- Docker
- Docker Compose
- Git

## Installation

### Option 1 : Installation Automatique (Recommandée)

Utilisez le script d'installation automatique qui configurera l'environnement pour vous :

```bash
chmod +x install-symfony.sh
./install-symfony.sh
```

Ce script va :

- Créer et configurer le fichier `.env`
- Générer les secrets de sécurité
- Configurer la base de données
- Installer les dépendances
- Configurer les permissions

### Option 2 : Installation Manuelle

Si vous préférez installer manuellement, suivez ces étapes :

1. Cloner le projet

```bash
git clone [URL_DU_PROJET]
cd [NOM_DU_PROJET]
```

2. Copier le fichier d'environnement

```bash
cp .env.dist .env
```

3. Construire et démarrer les conteneurs Docker

```bash
docker-compose up -d --build
```

4. Installer les dépendances Composer

```bash
docker-compose exec php composer install
```

5. Créer la base de données et exécuter les migrations (si nécessaire)

```bash
docker-compose exec php bin/console doctrine:database:create
docker-compose exec php bin/console doctrine:migrations:migrate
```

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
app/
├── src/           # Code source de l'application
├── templates/     # Templates Twig
├── public/        # Fichiers publics (images, CSS, JS)
├── tests/         # Tests unitaires et fonctionnels
└── var/          # Fichiers temporaires (cache, logs)
```

## Contribution

1. Créer une nouvelle branche pour votre fonctionnalité
2. Commiter vos changements
3. Créer une Pull Request

## Support

Pour toute question ou problème, veuillez ouvrir une issue dans le gestionnaire de tickets.
