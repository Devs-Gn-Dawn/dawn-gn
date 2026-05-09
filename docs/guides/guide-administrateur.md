# Guide administrateur

Ce guide vous accompagne dans l'administration complète de l'application Dawn GN.

## Accès à l'interface administrateur

Après connexion avec un compte ayant le rôle `ROLE_ADMIN`, vous êtes automatiquement redirigé vers l'interface administrateur.

## Gestion des utilisateurs

### Consulter la liste des utilisateurs

1. Accédez à "Gestion des utilisateurs" dans le menu
2. Vous verrez la liste de tous les utilisateurs
3. Utilisez le filtre pour afficher par rôle :
   - Tous les utilisateurs
   - Utilisateurs seulement (ROLE_USER)
   - Organisateurs (ROLE_ORGA)
   - Administrateurs (ROLE_ADMIN)

### Créer un utilisateur

1. Dans "Gestion des utilisateurs", cliquez sur "Créer un utilisateur"
2. Remplissez le formulaire :
   - Nom et prénom
   - Email (sera l'identifiant)
   - Mot de passe
   - Téléphone
   - Réseaux sociaux (optionnel)
   - Rôle(s) : Cochez les rôles à attribuer
   - Faction (optionnel, pour les organisateurs)
3. Cliquez sur "Créer"

L'utilisateur est créé et peut se connecter immédiatement.

### Modifier un utilisateur

1. Dans la liste des utilisateurs, cliquez sur "Modifier" sur un utilisateur
2. Modifiez les informations souhaitées :
   - Informations personnelles
   - Rôles
   - Faction
3. Cliquez sur "Enregistrer"

**Attention** : La modification des rôles change immédiatement les permissions de l'utilisateur.

### Supprimer un utilisateur

1. Dans la liste des utilisateurs, cliquez sur "Supprimer" sur un utilisateur
2. Confirmez la suppression

**Attention** : La suppression d'un utilisateur supprime également :
- Tous ses personnages
- Toutes ses inscriptions
- Tous ses contacts d'urgence, allergies, notes

Cette action est irréversible.

### Rechercher un utilisateur

Utilisez la barre de recherche pour trouver un utilisateur par :
- Nom
- Prénom
- Email

## Envoi d'invitations

### Inviter un nouvel utilisateur

1. Accédez à "Envoyer une invitation" dans le menu
2. Entrez l'email du nouvel utilisateur
3. Optionnellement, définissez le rôle initial
4. Cliquez sur "Envoyer l'invitation"

Un email avec un lien d'invitation est envoyé à l'adresse indiquée.

### Lien d'invitation

Le lien d'invitation permet à l'utilisateur de :
- Créer son compte
- Définir son mot de passe
- Compléter son profil

## Gestion des rôles

### Rôles disponibles

- **ROLE_USER** : Utilisateur standard (joueur)
- **ROLE_ORGA** : Organisateur (peut valider les personnages)
- **ROLE_ADMIN** : Administrateur (accès complet)

### Attribuer un rôle

1. Modifiez un utilisateur
2. Cochez les rôles à attribuer
3. Enregistrez

**Note** : Un utilisateur peut avoir plusieurs rôles. Par exemple, un organisateur peut aussi être joueur.

### Hiérarchie des rôles

Configuration Symfony (`config/packages/security.yaml`) :

- **`ROLE_ADMIN`** hérite de **`ROLE_ORGA`** et de **`ROLE_ALLOWED_TO_SWITCH`** (impersonation). Un administrateur accède donc aux écrans et API organisateur même sans rôle `ROLE_ORGA` explicite sur son compte.
- **`ROLE_ORGA`** n’est pas déclaré comme parent de `ROLE_USER` dans la hiérarchie : en pratique les comptes organisateur ont en général aussi `ROLE_USER` pour jouer.
- **`ROLE_USER`** est le rôle de base côté application (accès après connexion).

## Gestion globale

### Accès à toutes les fonctionnalités

En tant qu'administrateur, vous avez accès à :
- Toutes les fonctionnalités joueur
- Toutes les fonctionnalités organisateur
- Toutes les fonctionnalités administrateur

### Statistiques

Consultez les statistiques de l'application :
- Nombre total d'utilisateurs
- Nombre de personnages
- Nombre d'inscriptions
- Répartition par faction

### Configuration

Accédez aux paramètres de configuration :
- Variables d'environnement
- Configuration de la base de données
- Configuration du mailer

Consultez la [documentation de configuration](../configuration/configuration.md) pour plus de détails.

## Bonnes pratiques

1. **Sécurité** : Ne créez pas trop d'administrateurs
2. **Vérification** : Vérifiez les informations avant de créer un utilisateur
3. **Communication** : Utilisez les invitations pour les nouveaux utilisateurs
4. **Documentation** : Documentez les changements importants
5. **Sauvegarde** : Effectuez des sauvegardes régulières de la base de données

## Délégation

### Attribuer le rôle organisateur

Pour déléguer la validation des personnages :

1. Créez ou modifiez un utilisateur
2. Attribuez le rôle `ROLE_ORGA`
3. Optionnellement, assignez une faction pour filtrer les personnages
4. L'utilisateur peut maintenant valider les personnages

### Gestion par faction

Pour organiser la validation par faction :

1. Attribuez le rôle `ROLE_ORGA` aux organisateurs
2. Assignez une faction à chaque organisateur
3. Les organisateurs ne verront que les personnages de leur faction en validation
4. Ils peuvent toujours accéder à tous les personnages via "Tous les personnages"

## Dépannage

### Utilisateur ne peut pas se connecter

1. Vérifiez que l'utilisateur existe
2. Vérifiez que le mot de passe est correct
3. Utilisez la fonctionnalité de réinitialisation de mot de passe si nécessaire

### Problème de permissions

1. Vérifiez les rôles de l'utilisateur
2. Assurez-vous que les rôles sont correctement attribués
3. Vérifiez la configuration de sécurité dans `config/packages/security.yaml`

### Problème d'email

1. Vérifiez la configuration du mailer
2. Consultez les logs dans `app/var/log/`
3. Vérifiez que les emails ne sont pas dans les spams

## Navigation

- [Fonctionnalités](../02-fonctionnalites.md)
- [Architecture technique](../03-architecture.md)
- [Configuration](../configuration/configuration.md)
- [Dépannage](../configuration/depannage.md)
