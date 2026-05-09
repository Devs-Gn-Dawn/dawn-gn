# Guide organisateur

Ce guide vous accompagne dans l'utilisation de l'application Dawn GN en tant qu'organisateur.

## Accès à l'interface organisateur

Après connexion avec un compte ayant le rôle `ROLE_ORGA`, vous êtes automatiquement redirigé vers le tableau de bord organisateur (`/orga`). En accédant à la racine du site (`/`) une fois connecté·e, la même page s’affiche.

## Tableau de bord (`/orga`)

C’est la **page d’accueil** après connexion pour les comptes organisateur (sauf administrateurs, orientés vers `/admin`). La présentation reprend le même type de cartes que le tableau de bord administrateur.

Sur cette page, des indicateurs sont affichés pour **l’opus en cours** : ce sont les événements dont le statut de configuration est « ouvert » (`EventType`, même logique que pour les listes déroulantes d’inscription).

- **Personnes inscrites** : nombre de **comptes distincts** ayant au moins une inscription (`Registration`) sur cet opus (ou sur plusieurs opus si plusieurs sont ouverts en même temps — cas rare). Plusieurs billets pour le même compte ne comptent qu’une fois.
- **Principaux validés** : nombre de fiches **Principal** au statut **validé**, pour des joueurs **inscrits** à l’opus concerné (les rerolls et brouillons ne sont pas comptés).
- **Tableau par faction** : ventilation des deux indicateurs — les **inscrits** utilisent la faction **résolue** (`User::getResolvedFaction`) : **profil** si renseigné, sinon faction du **personnage principal** ; les **principaux validés** selon la **faction de la fiche** personnage. Une ligne supplémentaire apparaît s’il reste des cas sans faction résolue ou des fiches sans faction reconnue.

Si aucun opus n’est configuré comme ouvert, un message l’indique et les chiffres ne s’affichent pas.

## Validation des personnages

### Rouvrir le workflow (fiche validée ou bloquée)

Sur la fiche orga (`/orga/character/{id}/edit`), le bouton **« Rouvrir le workflow »** est proposé lorsque le personnage est **en cours de validation**, **validé** ou **rejeté** (état `REJETE`).

- Il repasse la fiche en **non validé** : le joueur peut à nouveau la modifier et la **soumettre** pour validation.
- Ce n’est **pas** le déverrouillage ligne à ligne des compétences / équipements / assets (`locked`).
- Un **e-mail** neutre est envoyé au joueur (« fiche rouverte pour modification »).

Pour un **refus** depuis la liste des personnages en validation, utilisez l’action **Rejeter** : le message joueur est celui d’un **non-validation**, pas celui d’une simple réouverture.

### Consulter les personnages en validation

1. Accédez à "Gestion des personnages" dans le menu
2. Vous verrez la liste des personnages avec le statut **"En cours de validation"**
3. Si vous avez une faction assignée, seuls les personnages de votre faction s'affichent

### Examiner un personnage

1. Cliquez sur un personnage dans la liste
2. Consultez toutes les informations :
   - Informations de base (nom, faction, classe)
   - Background
   - Compétences apprises
   - Équipements possédés
   - Assets spéciaux
   - XP utilisés et disponibles
   - Notes du joueur

### Valider un personnage

1. Après examen, cliquez sur "Valider"
2. Choisissez le type :
   - **Principal** : Personnage principal du joueur
   - **Secondaire** : Personnage secondaire
3. Ajoutez une note organisateur si nécessaire
4. Cliquez sur "Confirmer"

Le personnage est validé et le joueur est notifié par email.

### Rejeter un personnage

1. Si le personnage ne respecte pas les règles, cliquez sur "Rejeter"
2. Ajoutez des commentaires expliquant les raisons du rejet
3. Cliquez sur "Confirmer"

Le personnage retourne au joueur avec vos commentaires. Le joueur peut le modifier et le soumettre à nouveau.

## Gestion des personnages

### Éditer un personnage

1. Accédez à "Tous les personnages" ou sélectionnez un personnage
2. Cliquez sur "Éditer"
3. Vous pouvez modifier :
   - Informations de base
   - Background
   - Compétences
   - Équipements
   - Assets
   - XP Skills et XP Gear

### Type de fiche, faction et classe (corrections staff)

Sur la page d’édition orga (`/orga/character/{id}/edit`), en haut de la fiche :

- **Type de fiche (orga)** : définir le personnage comme Principal, Reroll ou Brouillon sans passer par le flux joueur. Si vous promouvez un personnage en principal ou reroll alors qu’une autre fiche du même joueur occupe déjà ce rôle, l’autre fiche est repassée en brouillon (confirmation affichée).
- **Faction / classe (orga)** : choisir la nouvelle faction puis la classe (liste chargée selon la faction). Utilisez **Prévisualiser** pour voir les compétences qui seront supprimées ; **Appliquer** demande une confirmation. Les PA dépensés sur les compétences retirées ne sont pas restitués ; équipements et assets ne sont pas modifiés automatiquement.

Ces actions sont possibles quel que soit l’état de validation du personnage.

### Ajouter des compétences à un personnage

1. Dans la page d'édition d'un personnage
2. Section "Compétences", cliquez sur "Ajouter une compétence"
3. Sélectionnez une compétence
4. Définissez le coût (peut différer du coût de base)
5. Ajoutez une note organisateur
6. Cliquez sur "Ajouter"

**Astuce** : Vous pouvez ajouter des compétences gratuitement en mettant le coût à 0.

### Ajouter des équipements à un personnage

1. Section "Équipements", cliquez sur "Ajouter un équipement"
2. Sélectionnez un équipement
3. Définissez le coût
4. Ajoutez une note organisateur
5. Cliquez sur "Ajouter"

### Ajouter des assets à un personnage

1. Section "Assets", cliquez sur "Ajouter un asset"
2. Sélectionnez un asset (capacité ou objet)
3. Pour les objets, définissez la quantité
4. Ajoutez une note organisateur
5. Cliquez sur "Ajouter"

**Note** : Les assets sont toujours gratuits (coût 0).

### Modifier les XP d'un personnage

1. Dans la page d'édition, section "Points d'expérience"
2. Vous pouvez ajuster :
   - **XP Skills** : Points alloués aux compétences
   - **XP Gear** : Points alloués aux équipements
3. Cliquez sur "Enregistrer"

**Attention** : Vérifiez que le personnage a assez d'XP pour ses compétences et équipements actuels.

### Verrouiller des éléments

Vous pouvez verrouiller des compétences, équipements ou assets pour empêcher leur modification :

1. Dans la liste des éléments, cliquez sur l'icône de verrouillage
2. L'élément est verrouillé et ne peut plus être modifié ou supprimé

### Ajouter des notes organisateur

Vous pouvez ajouter des notes privées visibles uniquement par les organisateurs :

1. Dans n'importe quelle section (compétences, équipements, assets)
2. Cliquez sur "Modifier" sur un élément
3. Ajoutez une note dans le champ "Note organisateur"
4. Cliquez sur "Enregistrer"

## Création de personnages pour des joueurs

1. Accédez à "Créer un personnage" dans le menu organisateur
2. Sélectionnez le joueur pour qui créer le personnage
3. Remplissez les informations comme pour un personnage normal
4. Vous pouvez définir directement le type (Principal ou Secondaire)
5. Le personnage est créé et validé automatiquement

## Gestion des joueurs

### Consulter les profils joueurs

1. Accédez à "Gestion des joueurs" dans le menu
2. Consultez la liste des joueurs
3. Cliquez sur un joueur pour voir son profil complet :
   - Informations personnelles
   - Personnages
   - Inscriptions aux événements
   - Contacts d'urgence
   - Allergies
   - Notes personnelles

### Contacter un joueur

1. Dans le profil d'un joueur, cliquez sur "Contacter"
2. Remplissez le formulaire :
   - Sujet
   - Message
3. Cliquez sur "Envoyer"

Un email est envoyé au joueur avec votre message.

## Filtrage par faction

Si vous avez une faction assignée dans votre profil :

- Vous ne voyez que les personnages de votre faction en validation
- Vous pouvez toujours accéder à tous les personnages via "Tous les personnages"
- Les emails sont envoyés depuis l'adresse de votre faction

## Bonnes pratiques

1. **Examinez attentivement** : Lisez bien le background et vérifiez la cohérence
2. **Vérifiez les XP** : Assurez-vous que les XP dépensés sont cohérents
3. **Communiquez** : Utilisez les notes organisateur et les commentaires de rejet
4. **Soyez cohérent** : Respectez les règles du jeu lors des validations
5. **Documentez** : Ajoutez des notes organisateur pour garder une trace

## Navigation

- [Fonctionnalités](../02-fonctionnalites.md)
- [Workflows](../04-workflows.md)
- [Structure des entités](../06-entites.md)
