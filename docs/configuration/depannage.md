# Guide de dépannage

Ce guide vous aide à résoudre les problèmes courants rencontrés avec l'application Dawn GN.

## Problèmes d'installation

### Les conteneurs Docker ne démarrent pas

**Symptômes** : `docker compose up` échoue ou les conteneurs s'arrêtent immédiatement.

**Solutions** :

1. Vérifiez que Docker est en cours d'exécution :
```bash
docker ps
```

2. Vérifiez que les ports ne sont pas utilisés :
```bash
# Port 8080 (application)
lsof -i :8080

# Port 8081 (Adminer)
lsof -i :8081

# Port 3306 (MySQL)
lsof -i :3306
```

3. Vérifiez les logs :
```bash
docker compose logs
```

4. Reconstruisez les conteneurs :
```bash
docker compose down
docker compose up -d --build
```

### Erreur "Port already in use"

**Symptômes** : Un port est déjà utilisé.

**Solutions** :

1. Identifiez le processus utilisant le port :
```bash
lsof -i :8080
```

2. Arrêtez le processus ou modifiez le port dans `docker-compose.yml`

3. Redémarrez les conteneurs :
```bash
docker compose down
docker compose up -d
```

## Problèmes de base de données

### Erreur de connexion à la base de données

**Symptômes** : "SQLSTATE[HY000] [2002] Connection refused" ou similaire.

**Solutions** :

1. Vérifiez que le conteneur MySQL est démarré :
```bash
docker compose ps
```

2. Attendez que MySQL soit complètement démarré (peut prendre 10-30 secondes)

3. Vérifiez la variable `DATABASE_URL` dans `.env` :
```env
DATABASE_URL="mysql://user:password@db:3306/database?serverVersion=8.0"
```

4. Testez la connexion :
```bash
docker compose exec db mysql -u root -p
```

5. Vérifiez les logs MySQL :
```bash
docker compose logs db
```

### Les migrations échouent

**Symptômes** : `make migrate` échoue avec une erreur.

**Solutions** :

1. Vérifiez le statut des migrations :
```bash
make migration-status
```

2. Vérifiez la connexion à la base de données

3. Vérifiez que la base de données existe :
```bash
docker compose exec db mysql -u root -p -e "SHOW DATABASES;"
```

4. Créez la base de données si nécessaire :
```bash
docker compose exec db mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS dawn_gn;"
```

5. Réessayez les migrations :
```bash
make migrate
```

## Problèmes d'authentification

### Impossible de se connecter

**Symptômes** : Le formulaire de connexion ne fonctionne pas.

**Solutions** :

1. Vérifiez que l'utilisateur existe dans la base de données

2. Vérifiez le hash du mot de passe :
```bash
docker compose exec web bin/console security:hash-password
```

3. Réinitialisez le mot de passe via l'interface `/reset-password`

4. Vérifiez les logs :
```bash
docker compose logs web | grep -i error
```

### Erreur "Invalid credentials"

**Symptômes** : Les identifiants sont corrects mais la connexion échoue.

**Solutions** :

1. Vérifiez que l'email existe dans la base de données

2. Vérifiez que le compte n'est pas désactivé

3. Videz le cache :
```bash
docker compose exec web bin/console cache:clear
```

4. Vérifiez la configuration de sécurité dans `app/config/packages/security.yaml`

## Problèmes de permissions

### Erreur "Permission denied" sur var/

**Symptômes** : Erreurs d'écriture dans `app/var/`.

**Solutions** :

1. Corrigez les permissions :
```bash
sudo chown -R $USER:$USER app/var
chmod -R 755 app/var
```

2. Dans Docker, les permissions sont généralement gérées automatiquement

3. Vérifiez les logs pour plus de détails :
```bash
docker compose logs web
```

### Erreur lors de l'upload de fichiers

**Solutions** :

1. Vérifiez `UPLOAD_MAX_FILESIZE` et `POST_MAX_SIZE` dans `.env`

2. Vérifiez les permissions du dossier d'upload

3. Vérifiez la configuration PHP :
```bash
docker compose exec web php -i | grep upload_max_filesize
```

## Problèmes de cache

### Le cache ne se vide pas

**Symptômes** : Les modifications ne sont pas prises en compte.

**Solutions** :

1. Videz le cache :
```bash
docker compose exec web bin/console cache:clear
```

2. Videz le cache pour un environnement spécifique :
```bash
docker compose exec web bin/console cache:clear --env=prod
```

3. Supprimez manuellement le cache :
```bash
rm -rf app/var/cache/*
```

4. Redémarrez les conteneurs :
```bash
docker compose restart
```

### Erreur "Cache directory is not writable"

**Solutions** :

1. Vérifiez les permissions :
```bash
ls -la app/var/cache
```

2. Corrigez les permissions :
```bash
chmod -R 777 app/var/cache
```

## Problèmes d'assets

### Les assets ne se compilent pas

**Symptômes** : Les fichiers CSS/JS ne sont pas à jour.

**Solutions** :

1. Installez les dépendances npm :
```bash
cd app && npm install
```

2. Compilez les assets :
```bash
make build
```

3. En développement, utilisez le mode watch :
```bash
make watch
```

4. Vérifiez les erreurs dans la console npm

### Erreur "Module not found"

**Solutions** :

1. Réinstallez les dépendances :
```bash
cd app && rm -rf node_modules && npm install
```

2. Vérifiez `package.json`

3. Vérifiez la configuration Webpack dans `app/webpack.config.js`

## Problèmes d'emails

### Les emails ne sont pas envoyés

**Symptômes** : Les emails ne partent pas.

**Solutions** :

1. Vérifiez la configuration `MAILER_DSN` dans `.env`

2. En développement, utilisez Mailtrap ou `null://null` pour logger sans envoyer

3. Vérifiez les logs :
```bash
docker compose logs web | grep -i mail
```

4. Testez la configuration :
```bash
docker compose exec web bin/console debug:mailer
```

### Erreur SMTP

**Solutions** :

1. Vérifiez les identifiants SMTP

2. Vérifiez que le serveur SMTP est accessible depuis le conteneur

3. Pour Gmail, utilisez un mot de passe d'application

4. Vérifiez les logs pour plus de détails

## Problèmes de performance

### L'application est lente

**Solutions** :

1. Activez le cache de production :
```bash
docker compose exec web bin/console cache:warmup --env=prod
```

2. Optimisez les autoloaders :
```bash
docker compose exec web composer dump-autoload --optimize --classmap-authoritative
```

3. Vérifiez les ressources Docker :
```bash
docker stats
```

4. Augmentez `PHP_MEMORY_LIMIT` dans `.env` si nécessaire

### La base de données est lente

**Solutions** :

1. Vérifiez les index de la base de données

2. Analysez les requêtes lentes :
```bash
docker compose exec db mysql -u root -p -e "SHOW PROCESSLIST;"
```

3. Optimisez les requêtes Doctrine

## Problèmes spécifiques

### Erreur "Class not found"

**Solutions** :

1. Régénérez les autoloaders :
```bash
docker compose exec web composer dump-autoload
```

2. Videz le cache :
```bash
docker compose exec web bin/console cache:clear
```

3. Vérifiez les namespaces dans le code

### Erreur "Route not found"

**Solutions** :

1. Vérifiez les routes :
```bash
docker compose exec web bin/console debug:router
```

2. Videz le cache de routing :
```bash
docker compose exec web bin/console cache:clear
```

3. Vérifiez la configuration des routes dans `app/config/routes/`

### Erreur lors de la génération PDF

**Solutions** :

1. Vérifiez que FPDF est installé :
```bash
docker compose exec web composer show | grep fpdf
```

2. Vérifiez les permissions d'écriture

3. Vérifiez les logs pour plus de détails

## Commandes utiles de débogage

### Voir les logs en temps réel

```bash
docker compose logs -f web
```

### Accéder au shell du conteneur

```bash
docker compose exec web bash
```

### Vérifier la configuration

```bash
docker compose exec web bin/console debug:config
```

### Tester la connexion à la base de données

```bash
docker compose exec web bin/console doctrine:query:sql "SELECT 1"
```

### Voir les variables d'environnement

```bash
docker compose exec web env | grep -E "(APP_|DATABASE_|MAILER_)"
```

## Obtenir de l'aide

Si le problème persiste :

1. Consultez les logs complets :
```bash
docker compose logs > logs.txt
```

2. Vérifiez la configuration :
```bash
docker compose exec web bin/console debug:config > config.txt
```

3. Ouvrez une issue avec :
   - Description du problème
   - Étapes pour reproduire
   - Logs et configuration (sans informations sensibles)

## Navigation

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Documentation principale](../README.md)
