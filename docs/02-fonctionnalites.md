# Fonctionnalités de l'application

Cette page détaille toutes les fonctionnalités disponibles dans l'application Dawn GN, organisées par type d'utilisateur.

## Fonctionnalités pour les joueurs

### Gestion de compte

- **Inscription** : Création d'un compte avec email, nom, prénom, téléphone
- **Connexion** : Authentification par email et mot de passe
- **Réinitialisation de mot de passe** : Récupération de mot de passe par email
- **Gestion du profil** : Modification des informations personnelles

### Gestion de profil

- **Informations personnelles** : Nom, prénom, email, téléphone, réseaux sociaux
- **Contacts d'urgence** : Ajout, modification et suppression de contacts d'urgence
- **Allergies** : Gestion des allergies alimentaires et médicales
- **Notes personnelles** : Notes privées pour le joueur
- **Droit à l'image** : Gestion du consentement pour l'utilisation d'images

### Gestion de personnages

#### Création de personnage
- Création de personnages de type :
  - **Principal** : Personnage principal du joueur
  - **Secondaire** : Personnage secondaire
  - **Brouillon** : Personnage en cours de création

#### Configuration du personnage
- **Nom** : Nom du personnage
- **Faction** : Choix parmi Nomads, Tech'ers, Rodoir, Néo-cuba
- **Classe** : Choix de classe selon la faction sélectionnée
- **Background** : Rédaction de l'histoire et du background du personnage
- **Description** : Description physique et psychologique

#### Système de compétences
- Consultation du catalogue de compétences disponibles
- Ajout de compétences avec coût en XP Skills
- Gestion des compétences apprises
- Notes personnelles sur les compétences
- Notes de l'organisateur (visibles après validation)

#### Système d'équipement
- Consultation du catalogue d'équipements disponibles
- Ajout d'équipements avec coût en XP Gear
- Gestion des possessions (équipements possédés)
- Notes personnelles sur les équipements
- Notes de l'organisateur (visibles après validation)

#### Assets (capacités et objets)
- Ajout d'assets spéciaux (capacités, objets)
- Assets gratuits (coût 0 XP)
- Gestion des quantités pour les objets
- Notes personnelles et notes organisateur

#### Points d'expérience
- Visualisation des XP disponibles
- Répartition entre XP Skills et XP Gear
- Calcul automatique des XP gagnés selon les événements
- Suivi des XP dépensés

#### Validation
- Soumission du personnage pour validation
- Suivi du statut de validation :
  - Non validé
  - En cours de validation
  - Validé
  - Rejeté

### Inscriptions aux événements

- **Consultation des événements** : Liste des événements Dawn disponibles
- **Inscription** : Enregistrement à un événement
- **Ticket HelloAsso** : Enregistrement du numéro de ticket HelloAsso
- **Historique** : Consultation de l'historique des inscriptions
- **Gain d'XP** : Attribution automatique de 3 XP après participation à un événement terminé

### Génération de fiche de personnage

- **Export PDF** : Génération d'une fiche de personnage complète en PDF
- **Contenu de la fiche** :
  - Informations du personnage
  - Compétences apprises
  - Équipements et possessions
  - Assets spéciaux
  - Background
  - Statistiques (PV max, armure, résistance aux radiations)

## Fonctionnalités pour les organisateurs

### Validation des personnages

- **Consultation des personnages en validation** : Liste des personnages soumis pour validation
- **Validation** : Approbation d'un personnage (devient principal ou secondaire)
- **Rejet** : Refus d'un personnage avec possibilité de commentaires
- **Filtrage par faction** : Filtrage des personnages selon la faction de l'organisateur

### Gestion des personnages

- **Consultation de tous les personnages** : Accès à tous les personnages validés
- **Édition des personnages** : Modification des personnages validés
- **Ajout de compétences** : Ajout de compétences aux personnages
- **Ajout d'équipements** : Ajout d'équipements aux personnages
- **Ajout d'assets** : Ajout d'assets spéciaux aux personnages
- **Modification des XP** : Ajustement des points d'expérience
- **Notes organisateur** : Ajout de notes privées pour l'organisation
- **Verrouillage** : Possibilité de verrouiller des éléments (compétences, équipements, assets)

### Gestion des joueurs

- **Consultation des profils** : Accès aux profils des joueurs
- **Contacts d'urgence** : Consultation des contacts d'urgence
- **Allergies** : Consultation des allergies des joueurs
- **Notes** : Consultation des notes personnelles des joueurs
- **Historique** : Consultation de l'historique des inscriptions

### Communication

- **Envoi d'emails** : Envoi d'emails aux joueurs via l'interface
- **Contact par faction** : Envoi d'emails aux joueurs d'une faction spécifique
- **Templates d'emails** : Utilisation de templates pour les communications

### Création de personnages

- **Création pour un joueur** : Création de personnages au nom d'un joueur
- **Configuration complète** : Création avec toutes les options disponibles

## Fonctionnalités pour les administrateurs

### Gestion des utilisateurs

- **Liste des utilisateurs** : Consultation de tous les utilisateurs
- **Création d'utilisateur** : Création de nouveaux comptes utilisateurs
- **Modification d'utilisateur** : Modification des informations utilisateur
- **Suppression d'utilisateur** : Suppression de comptes (avec cascade sur les personnages)
- **Gestion des rôles** : Attribution et modification des rôles (USER, ORGA, ADMIN)
- **Filtrage** : Filtrage des utilisateurs par rôle
- **Recherche** : Recherche d'utilisateurs par nom, email

### Envoi d'invitations

- **Invitation par email** : Envoi d'invitations aux nouveaux utilisateurs
- **Génération de liens** : Génération de liens d'invitation personnalisés
- **Suivi** : Suivi des invitations envoyées

### Administration globale

- **Accès complet** : Accès à toutes les fonctionnalités de l'application
- **Gestion des événements** : Configuration des événements Dawn
- **Statistiques** : Consultation des statistiques de l'application
- **Configuration** : Accès aux paramètres de configuration

### Gestion des rôles

- **Attribution de rôles** : Attribution des rôles ROLE_USER, ROLE_ORGA, ROLE_ADMIN
- **Modification de rôles** : Changement de rôle des utilisateurs
- **Permissions** : Gestion des permissions selon les rôles

## Rôles et permissions

### ROLE_USER
- Accès aux fonctionnalités joueur uniquement
- Création et gestion de ses propres personnages
- Inscription aux événements
- Gestion de son profil

### ROLE_ORGA
- Accès aux fonctionnalités organisateur
- Accès aux fonctionnalités joueur
- Validation des personnages
- Gestion des personnages
- Communication avec les joueurs

### ROLE_ADMIN
- Accès complet à toutes les fonctionnalités
- Gestion des utilisateurs
- Envoi d'invitations
- Administration globale

## Navigation

- [Workflows détaillés](04-workflows.md)
- [Guide joueur](guides/guide-joueur.md)
- [Guide organisateur](guides/guide-organisateur.md)
- [Guide administrateur](guides/guide-administrateur.md)
