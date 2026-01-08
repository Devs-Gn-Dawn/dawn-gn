# API et endpoints

Cette page documente les principaux endpoints API de l'application Dawn GN.

## Routes publiques

### Authentification

- `GET /login` - Page de connexion
- `POST /login` - Traitement de la connexion
- `GET /logout` - Déconnexion
- `GET /register` - Page d'inscription
- `POST /register` - Traitement de l'inscription

### Réinitialisation de mot de passe

- `GET /reset-password` - Demande de réinitialisation
- `POST /reset-password` - Traitement de la demande
- `GET /reset-password/check-email` - Vérification de l'email
- `GET /reset-password/reset/{token}` - Réinitialisation avec token
- `POST /reset-password/reset/{token}` - Traitement de la réinitialisation

### Contact

- `POST /api/contact` - Envoi d'un message de contact

### Pages statiques

- `GET /pages/{slug}` - Affichage d'une page statique

## Routes joueur (ROLE_USER)

### Personnages

- `GET /characters` - Liste des personnages du joueur
- `GET /characters/new` - Formulaire de création
- `POST /characters/new` - Création d'un personnage
- `GET /characters/main` - Redirection vers le personnage principal
- `GET /characters/{id}/check` - Vérification d'un personnage
- `GET /characters/{id}/edit` - Édition d'un personnage
- `POST /characters/{id}/edit` - Sauvegarde des modifications
- `POST /characters/{id}/delete` - Suppression d'un personnage
- `POST /characters/{id}/submit` - Soumission pour validation
- `POST /characters/{id}/set-type/{type}` - Définition du type

### API Personnages

- `GET /api/classes/{faction}` - Liste des classes pour une faction
- `GET /api/character/{id}/background` - Récupération du background
- `POST /api/character/{id}/background` - Mise à jour du background
- `GET /api/character/{id}/available-skills` - Compétences disponibles
- `POST /api/character/{id}/skill/add` - Ajout d'une compétence
- `POST /api/character/{id}/skill/delete` - Suppression d'une compétence
- `POST /api/character/{id}/skill/xp/add` - Ajout d'XP Skills
- `GET /api/character/{id}/available-gear` - Équipements disponibles
- `POST /api/character/{id}/gear/add` - Ajout d'un équipement
- `POST /api/character/{id}/gear/delete` - Suppression d'un équipement
- `POST /api/character/{id}/gear/xp/add` - Ajout d'XP Gear
- `POST /api/character/{id}/name/update` - Mise à jour du nom

### Compte utilisateur

- `GET /account` - Page du compte
- `POST /account/profile/edit` - Modification du profil
- `POST /account/login/edit` - Modification des identifiants

### Contacts d'urgence

- `POST /account/emergency-contact/add` - Ajout d'un contact
- `POST /account/emergency-contact/{id}/edit` - Modification d'un contact
- `POST /account/emergency-contact/{id}/delete` - Suppression d'un contact

### Allergies

- `POST /allergy/add` - Ajout d'une allergie
- `POST /allergy/{id}/edit` - Modification d'une allergie
- `POST /allergy/{id}/delete` - Suppression d'une allergie

### Notes

- `POST /note/add` - Ajout d'une note
- `POST /note/{id}/edit` - Modification d'une note
- `POST /note/{id}/delete` - Suppression d'une note

### Inscriptions

- `POST /account/registration/add` - Ajout d'une inscription
- `POST /account/registration/{id}/edit` - Modification d'une inscription

### PDF

- `GET /pdf/{id}` - Génération de la fiche de personnage en PDF

## Routes organisateur (ROLE_ORGA)

### Gestion des personnages

- `GET /orga` - Page d'accueil organisateur
- `GET /orga/characters` - Personnages en validation
- `GET /orga/all_characters` - Tous les personnages
- `GET /orga/character/{id}/edit` - Édition d'un personnage
- `POST /api/character/{id}/validate` - Validation d'un personnage
- `POST /api/character/{id}/reject` - Rejet d'un personnage
- `POST /api/create_character` - Création d'un personnage pour un joueur

### API Personnages (Organisateur)

- `GET /api/character/{id}/assets` - Assets disponibles pour un personnage
- `POST /api/character/{id}/asset/add` - Ajout d'un asset
- `POST /api/character/{id}/asset/edit` - Modification d'un asset
- `POST /api/character/asset/{characterAssetId}/delete` - Suppression d'un asset
- `POST /api/character/{id}/skill/edit` - Modification d'une compétence
- `POST /api/character/{id}/gear/edit` - Modification d'un équipement

### Gestion des joueurs

- `GET /orga/players` - Liste des joueurs
- `GET /orga/player/{id}` - Profil d'un joueur
- `POST /api/contact_player` - Contact d'un joueur par email

## Routes administrateur (ROLE_ADMIN)

### Gestion des utilisateurs

- `GET /admin` - Page d'accueil administrateur
- `GET /admin/users` - Liste des utilisateurs
- `POST /api/user/create` - Création d'un utilisateur
- `POST /api/user/{id}/update` - Modification d'un utilisateur
- `POST /api/user/{id}/delete` - Suppression d'un utilisateur
- `POST /api/user/filter` - Filtrage des utilisateurs

### Invitations

- `GET /admin/send-invite` - Page d'envoi d'invitation
- `POST /api/send-invite` - Envoi d'une invitation

## Exemples de requêtes API

### Création d'un personnage

```http
POST /characters/new
Content-Type: application/json

{
  "character_name": "John Doe",
  "faction": "Nomads",
  "class": "Runner",
  "background": "Histoire du personnage..."
}
```

### Ajout d'une compétence

```http
POST /api/character/1/skill/add
Content-Type: application/json

{
  "skill_id": 5,
  "cost": 3,
  "note": "Note personnelle"
}
```

### Validation d'un personnage

```http
POST /api/character/1/validate
Content-Type: application/json

{
  "type": "MAIN",
  "note_orga": "Personnage validé"
}
```

### Création d'un utilisateur (Admin)

```http
POST /api/user/create
Content-Type: application/json

{
  "name": "Dupont",
  "firstname": "Jean",
  "email": "jean.dupont@example.com",
  "password": "motdepasse",
  "roles": ["ROLE_USER"]
}
```

## Format des réponses

### Succès

```json
{
  "success": true,
  "message": "Opération réussie"
}
```

### Erreur

```json
{
  "success": false,
  "error": "Message d'erreur"
}
```

## Authentification

Toutes les routes API (sauf celles marquées comme publiques) nécessitent une authentification. L'authentification se fait via les sessions Symfony après connexion sur `/login`.

## Permissions

Les routes sont protégées par des attributs `#[IsGranted]` :

- `ROLE_USER` : Accès aux routes joueur
- `ROLE_ORGA` : Accès aux routes organisateur + joueur
- `ROLE_ADMIN` : Accès à toutes les routes

## Navigation

- [Architecture technique](03-architecture.md)
- [Structure des entités](06-entites.md)
- [Workflows](04-workflows.md)
