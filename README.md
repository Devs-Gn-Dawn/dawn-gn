# Dawn GN - Application de Gestion de Jeu de Rôle Grandeur Nature

Application web Symfony pour la gestion complète d'un jeu de rôle grandeur nature (GN), permettant aux joueurs de créer et gérer leurs personnages, et aux organisateurs de valider et administrer les inscriptions aux événements.

## Vue d'ensemble

Dawn GN est une plateforme web complète qui facilite :

- La création et la gestion de personnages de jeu de rôle
- L'inscription aux événements Dawn
- La validation des personnages par les organisateurs
- L'administration des utilisateurs et des événements

## Documentation complète

📚 **La documentation complète est disponible dans le dossier [`docs/`](docs/README.md)**

### Navigation rapide

- [📖 Présentation de l'application](docs/01-presentation.md)
- [⚙️ Fonctionnalités](docs/02-fonctionnalites.md)
- [🏗️ Architecture technique](docs/03-architecture.md)
- [🔄 Workflows](docs/04-workflows.md)
- [⭐ Système de points d'expérience](docs/05-systeme-xp.md)
- [📊 Structure des entités](docs/06-entites.md)
- [🔌 API et endpoints](docs/07-api.md)

### Guides d'utilisation

- [👤 Guide joueur](docs/guides/guide-joueur.md)
- [👥 Guide organisateur](docs/guides/guide-organisateur.md)
- [🔧 Guide administrateur](docs/guides/guide-administrateur.md)

### Installation et configuration

- [🚀 Installation](docs/configuration/installation.md)
- [⚙️ Configuration](docs/configuration/configuration.md)
- [🔧 Dépannage](docs/configuration/depannage.md)

## Démarrage rapide

### Prérequis

- Docker et Docker Compose
- Git
- Node.js et npm (pour le développement local)

### Installation rapide

1. Cloner le projet :

```bash
git clone [URL_DU_PROJET]
cd dawn-gn
```

2. Démarrer les conteneurs Docker :

```bash
make up
```

3. Installer les dépendances :

```bash
make install
```

4. Accéder à l'application :

- Application : http://localhost:8080
- Adminer (base de données) : http://localhost:8081

Pour plus de détails, consultez le [guide d'installation complet](docs/configuration/installation.md).

## Commandes principales

- `make up` : Démarre les conteneurs Docker
- `make down` : Arrête les conteneurs Docker
- `make install` : Installe toutes les dépendances (Composer + npm)
- `make build` : Compile les assets pour la production
- `make watch` : Lance le watcher pour le développement
- `make migrate` : Exécute les migrations Doctrine
- `make bash` : Ouvre un terminal dans le conteneur PHP

## Technologies utilisées

- **Backend** : Symfony 7.2 (PHP 8.1+)
- **Base de données** : MySQL 8.0
- **Frontend** : Twig, Tailwind CSS, Webpack Encore
- **Infrastructure** : Docker Compose

## Contribution

1. Créer une nouvelle branche pour votre fonctionnalité
2. Commiter vos changements
3. Créer une Pull Request

## Support

Pour toute question ou problème, veuillez ouvrir une issue dans le gestionnaire de tickets.
