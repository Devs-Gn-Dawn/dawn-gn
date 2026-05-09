# Architecture technique

Cette page décrit l'architecture technique de l'application Dawn GN, les technologies utilisées et la structure du projet.

## Stack technique

### Backend

- **Framework** : Symfony 7.2
- **Langage** : PHP 8.1+
- **ORM** : Doctrine ORM 2.17
- **Migrations** : Doctrine Migrations Bundle 3.3
- **Sécurité** : Symfony Security Bundle
- **Validation** : Symfony Validator
- **Formulaires** : Symfony Form

### Base de données

- **SGBD** : MySQL 8.0
- **ORM** : Doctrine ORM
- **Migrations** : Doctrine Migrations

### Frontend

- **Templates** : Twig 2.12/3.0
- **CSS** : Tailwind CSS
- **JavaScript** : Webpack Encore
- **Asset Mapper** : Symfony Asset Mapper

### Infrastructure

- **Conteneurisation** : Docker Compose
- **Serveur web** : Apache (dans conteneur Docker)
- **PHP** : PHP 8.1+ (dans conteneur Docker)
- **Outils** : Adminer pour la gestion de base de données

### Bibliothèques et outils

- **Génération PDF** : FPDF (setasign/fpdf)
- **QR Code** : endroid/qr-code
- **Email** : Symfony Mailer
- **Logging** : Monolog Bundle
- **Tests** : PHPUnit 9.5
- **Réinitialisation de mot de passe** : SymfonyCasts Reset Password Bundle

## Structure du projet

```
dawn-gn/
├── app/                          # Application Symfony
│   ├── assets/                   # Fichiers source JS et CSS
│   ├── bin/                      # Exécutables
│   │   ├── console              # Console Symfony
│   │   └── phpunit              # PHPUnit
│   ├── config/                   # Configuration
│   │   ├── packages/            # Configuration des bundles
│   │   └── routes/             # Configuration des routes
│   ├── migrations/              # Migrations Doctrine
│   ├── public/                  # Point d'entrée public
│   │   └── index.php           # Front controller
│   ├── src/                     # Code source PHP
│   │   ├── Controller/         # Contrôleurs
│   │   ├── Entity/             # Entités Doctrine
│   │   ├── Repository/         # Repositories Doctrine
│   │   ├── Service/             # Services métier
│   │   ├── DTO/                # Data Transfer Objects
│   │   └── Kernel.php          # Kernel Symfony
│   ├── templates/               # Templates Twig
│   │   ├── account/            # Templates compte
│   │   ├── admin/              # Templates admin
│   │   ├── character/          # Templates personnages
│   │   ├── orga/               # Templates organisateur
│   │   └── partials/           # Partiels réutilisables
│   ├── tests/                   # Tests PHPUnit
│   ├── translations/            # Fichiers de traduction
│   ├── var/                     # Fichiers temporaires
│   │   └── cache/              # Cache Symfony
│   └── vendor/                  # Dépendances Composer
├── docker/                      # Configuration Docker
│   ├── apache/                 # Configuration Apache
│   │   └── vhost.conf         # Virtual host
│   └── php/                    # Configuration PHP
│       ├── Dockerfile          # Image Docker PHP
│       ├── docker-entrypoint.sh # Script d'entrée
│       └── php.ini             # Configuration PHP
├── docs/                        # Documentation
├── config/                      # Configuration globale
│   └── packages/
│       └── doctrine.yaml       # Configuration Doctrine
├── .env.dist                    # Template variables d'environnement
├── docker-compose.yml           # Configuration Docker Compose
├── Makefile                     # Commandes Make
└── README.md                    # Documentation principale
```

## Architecture de l'application

### Pattern MVC avec Services

L'application suit le pattern Model-View-Controller avec une couche de services :

- **Model** : Entités Doctrine dans `src/Entity/`
- **View** : Templates Twig dans `templates/`
- **Controller** : Contrôleurs Symfony dans `src/Controller/`
- **Service** : Services métier dans `src/Service/` pour centraliser la logique métier

### Contrôleurs principaux

- `HomeController` : Page d'accueil `/` et redirections (admin → admin, orga → `/orga`, joueur → liste personnages)
- `CharacterController` : Gestion des personnages (joueurs)
- `OrgaController` : Gestion organisateur (tableau de bord `/orga` avec statistiques opus, validation, édition)
- `FormLoginSuccessHandler` (`src/Security/`) : cible après `POST /login` selon le rôle (cohérent avec `HomeController`)
- `AdminController` : Administration (utilisateurs, invitations)
- `AccountController` : Gestion du compte utilisateur
- `SecurityController` : Authentification
- `RegistrationController` : Inscription
- `ResetPasswordController` : Réinitialisation de mot de passe
- `PdfController` : Génération de PDF
- `PagesController` : Pages statiques

### Entités principales

- `User` : Utilisateurs de l'application
- `Character` : Personnages de jeu
- `Registration` : Inscriptions aux événements
- `Skill` / `SkillLearned` : Compétences
- `Gear` / `Possession` : Équipements
- `Asset` / `CharacterAsset` : Assets spéciaux
- `EmergencyContact` : Contacts d'urgence
- `Allergy` : Allergies
- `Note` : Notes personnelles

### Repositories

Chaque entité possède son repository pour les requêtes personnalisées (ex. agrégations orga sur `Registration` et `Character` pour le tableau de bord `/orga`) :

- `UserRepository`
- `CharacterRepository`
- `RegistrationRepository`
- `SkillRepository`
- `GearRepository`
- `AssetRepository`
- etc.

### Services

Services métier pour centraliser la logique et réduire la duplication :

- **EmailService** : Centralise tous les envois d'emails (invitations, contacts, validations, réinitialisation de mot de passe)
- **CharacterService** : Gestion complète des personnages (création, compétences, équipements, assets, XP, validation)
- **UserService** : Gestion des utilisateurs (profil, identifiants, création, filtrage)

Les services sont injectés dans les contrôleurs via l'injection de dépendances de Symfony.

## Sécurité

### Authentification

- Authentification par formulaire (email/mot de passe)
- Hashage des mots de passe avec Symfony Password Hasher
- Sessions Symfony

### Autorisation

- Système de rôles :
  - `ROLE_USER` : Utilisateur standard
  - `ROLE_ORGA` : Organisateur
  - `ROLE_ADMIN` : Administrateur
- Contrôle d'accès par attributs `#[IsGranted]`
- Firewall Symfony configuré dans `config/packages/security.yaml`

## Base de données

### Configuration

- Configuration Doctrine dans `config/packages/doctrine.yaml`
- Migrations dans `app/migrations/`
- Connexion via variable d'environnement `DATABASE_URL`

### Relations principales

- User → Characters (OneToMany)
- User → Registrations (OneToMany)
- User → EmergencyContacts (OneToMany)
- User → Allergies (OneToMany)
- Character → SkillsLearned (OneToMany)
- Character → Possessions (OneToMany)
- Character → CharacterAssets (OneToMany)

## Docker

### Services

- **web** : Conteneur PHP/Apache
  - Port 8080 (HTTP)
  - Port 9003 (Xdebug)
- **db** : Conteneur MySQL 8.0
  - Port 3306
- **adminer** : Interface web pour MySQL
  - Port 8081

### Volumes

- `./app` : Code source de l'application
- `mysql_data` : Données MySQL persistantes

## Développement

### Commandes Make

- `make up` : Démarre les conteneurs
- `make down` : Arrête les conteneurs
- `make install` : Installe les dépendances
- `make build` : Compile les assets
- `make watch` : Mode watch pour le développement
- `make migrate` : Exécute les migrations
- `make bash` : Accès au conteneur PHP

### Dépendances

- **Composer** : Gestion des dépendances PHP
- **npm** : Gestion des dépendances JavaScript/CSS
- **Webpack Encore** : Compilation des assets

## Tests

- Framework : PHPUnit 9.5
- Configuration : `app/phpunit.xml.dist`
- Tests : `app/tests/`
- Exécution : `make tests` ou `docker-compose exec web bin/phpunit`

## Performance

- Cache Symfony activé
- Cache de templates Twig
- Optimisations Doctrine (lazy loading, requêtes optimisées)

## Navigation

- [Structure des entités](06-entites.md)
- [API et endpoints](07-api.md)
- [Configuration](configuration/configuration.md)
