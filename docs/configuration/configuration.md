# Configuration

Cette page décrit la configuration de l'application Dawn GN.

## Variables d'environnement

L'application utilise des variables d'environnement définies dans les fichiers `.env`.

### Fichiers d'environnement

- `.env` : Configuration principale (production)
- `.env.dev` : Configuration pour le développement
- `.env.test` : Configuration pour les tests
- `.env.dist` : Template de configuration

### Variables principales

#### Application

```env
APP_ENV=dev                    # Environnement (dev, prod, test)
APP_SECRET=change_this_secret  # Secret pour la sécurité (générer avec secrets:generate-keys)
APP_DEBUG=1                    # Mode debug (1 pour dev, 0 pour prod)
```

#### Base de données

```env
DATABASE_URL="mysql://db_user:db_password@db:3306/db_name?serverVersion=8.0"
MYSQL_ROOT_PASSWORD=change_this_password
MYSQL_DATABASE=dawn_gn
MYSQL_USER=dawn_user
MYSQL_PASSWORD=change_this_password
```

**Format DATABASE_URL** :
```
mysql://[user]:[password]@[host]:[port]/[database]?serverVersion=[version]
```

#### Docker

```env
PHP_MEMORY_LIMIT=512M
UPLOAD_MAX_FILESIZE=100M
POST_MAX_SIZE=100M
```

#### Chemins de l'application

```env
APP_PATH=/var/www/app
PUBLIC_PATH=/var/www/app/public
VAR_PATH=/var/www/app/var
VENDOR_PATH=/var/www/app/vendor
```

#### Mailer

```env
MAILER_DSN=null://null
```

Pour configurer l'envoi d'emails, configurez `MAILER_DSN` :

- **SMTP** : `smtp://user:pass@smtp.example.com:587`
- **Gmail** : `gmail://user:pass@default`
- **Mailtrap** : `smtp://user:pass@smtp.mailtrap.io:2525`
- **Null** (développement) : `null://null`

#### Messenger

```env
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

## Configuration Symfony

### Fichiers de configuration

Les fichiers de configuration se trouvent dans `app/config/packages/` :

- `doctrine.yaml` : Configuration Doctrine/ORM
- `security.yaml` : Configuration de la sécurité
- `framework.yaml` : Configuration du framework
- `mailer.yaml` : Configuration du mailer
- `monolog.yaml` : Configuration des logs
- `webpack_encore.yaml` : Configuration des assets

### Doctrine

Configuration de la base de données dans `app/config/packages/doctrine.yaml` :

```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        auto_generate_proxy_classes: true
        mappings:
            App:
                is_bundle: false
                type: attribute
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

### Sécurité

Configuration de la sécurité dans `app/config/packages/security.yaml` :

- **Providers** : Fournisseurs d'utilisateurs
- **Firewalls** : Configuration des firewalls
- **Access Control** : Contrôle d'accès par route
- **Role Hierarchy** : Hiérarchie des rôles

### Mailer

Configuration dans `app/config/packages/mailer.yaml` :

```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
```

## Configuration Docker

### docker-compose.yml

Le fichier `docker-compose.yml` définit les services :

- **web** : Conteneur PHP/Apache
- **db** : Conteneur MySQL
- **adminer** : Interface web pour MySQL
- **mailhog** : Serveur SMTP de test pour le développement local (ports 1025 SMTP, 8025 web)

### Dockerfile PHP

Le fichier `docker/php/Dockerfile` définit l'image PHP avec :
- PHP 8.1+
- Extensions nécessaires
- Configuration Apache
- Xdebug pour le développement

### Configuration Apache

Le fichier `docker/apache/vhost.conf` configure le virtual host Apache.

## Configuration des assets

### Webpack Encore

Configuration dans `app/webpack.config.js` et `app/tailwind.config.js`.

### Compilation des assets

```bash
make build    # Production
make dev      # Développement
make watch    # Watch mode
```

## Configuration de la base de données

### Connexion

La connexion se fait via la variable `DATABASE_URL` :

```env
DATABASE_URL="mysql://user:password@db:3306/database?serverVersion=8.0"
```

### Migrations

Les migrations se trouvent dans `app/migrations/`.

Commandes utiles :

```bash
make migrate              # Exécuter les migrations
make migration-status     # Voir le statut
```

### Créer une migration

```bash
docker compose exec web bin/console make:migration
```

## Configuration du mailer

### SMTP standard

```env
MAILER_DSN=smtp://user:password@smtp.example.com:587
```

### Gmail

```env
MAILER_DSN=gmail://user:password@default
```

**Note** : Pour Gmail, vous devrez peut-être activer "Accès aux applications moins sécurisées" ou utiliser un mot de passe d'application.

### Mailtrap (développement)

```env
MAILER_DSN=smtp://user:password@smtp.mailtrap.io:2525
```

### Null (pas d'envoi)

```env
MAILER_DSN=null://null
```

Les emails sont loggés mais pas envoyés.

### MailHog (développement local)

MailHog est un serveur SMTP de test intégré dans Docker pour le développement local. Il capture tous les emails envoyés et permet de les visualiser via une interface web.

**Configuration dans `.env.dev`** :

```env
MAILER_DSN=smtp://mailhog:1025
```

**Utilisation** :

1. Démarrer les conteneurs Docker : `make up` ou `docker compose up -d`
2. Les emails envoyés par l'application sont automatiquement capturés par MailHog
3. Accéder à l'interface web sur `http://localhost:8025` pour visualiser les emails reçus

**Avantages** :
- Aucune configuration SMTP externe nécessaire
- Visualisation des emails avec leur contenu HTML/text
- Test des emails sans risque d'envoi réel
- Interface web intuitive pour déboguer les emails

## Configuration des logs

Les logs sont configurés dans `app/config/packages/monolog.yaml`.

Emplacement des logs : `app/var/log/`

### Niveaux de log

- `DEBUG` : Messages de débogage
- `INFO` : Informations générales
- `NOTICE` : Notifications
- `WARNING` : Avertissements
- `ERROR` : Erreurs
- `CRITICAL` : Erreurs critiques

## Configuration de la production

### Variables d'environnement

Pour la production, configurez :

```env
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<secret_généré>
```

### Optimisations

```bash
# Vider le cache
docker compose exec web bin/console cache:clear --env=prod

# Compiler les assets
make build

# Optimiser les autoloaders
docker compose exec web composer dump-autoload --optimize --classmap-authoritative
```

## Sécurité

### Secrets

Générez un secret unique pour chaque environnement :

```bash
docker compose exec web bin/console secrets:generate-keys
```

### Mots de passe

- Utilisez des mots de passe forts
- Ne commitez jamais les fichiers `.env`
- Utilisez des secrets différents pour chaque environnement

## Vérification de la configuration

### Vérifier la configuration Symfony

```bash
docker compose exec web bin/console debug:config
```

### Vérifier les routes

```bash
docker compose exec web bin/console debug:router
```

### Vérifier les services

```bash
docker compose exec web bin/console debug:container
```

## Navigation

- [Installation](installation.md)
- [Dépannage](depannage.md)
- [Architecture technique](../03-architecture.md)
