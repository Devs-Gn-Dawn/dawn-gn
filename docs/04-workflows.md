# Workflows de l'application

Cette page décrit les workflows principaux de l'application Dawn GN avec des diagrammes pour faciliter la compréhension.

## Workflow de création de personnage

Ce workflow décrit le processus complet de création d'un personnage par un joueur.

```mermaid
flowchart TD
    Start([Joueur se connecte]) --> Create[Créer un nouveau personnage]
    Create --> Draft[Personnage créé en mode BROUILLON]
    Draft --> FillInfo[Remplir informations de base]
    FillInfo --> ChooseFaction[Choisir faction]
    ChooseFaction --> ChooseClass[Choisir classe selon faction]
    ChooseClass --> WriteBackground[Rédiger le background]
    WriteBackground --> AddSkills[Ajouter compétences avec XP Skills]
    AddSkills --> AddGear[Ajouter équipements avec XP Gear]
    AddGear --> AddAssets[Ajouter assets spéciaux]
    AddAssets --> CheckXP{Vérifier XP disponibles}
    CheckXP -->|XP insuffisants| AddSkills
    CheckXP -->|XP OK| Submit[Soumettre pour validation]
    Submit --> Waiting[Statut: EN_COURS]
    Waiting --> OrgaReview[Organisateur examine]
    OrgaReview --> Decision{Validation?}
    Decision -->|Validé| Validated[Statut: VALIDE]
    Decision -->|Rejeté| Rejected[Statut: REJETE]
    Validated --> SetType{Définir type}
    SetType -->|Principal| Main[Type: MAIN]
    SetType -->|Secondaire| Secondary[Type: SECONDARY]
    Main --> End([Personnage prêt])
    Secondary --> End
    Rejected --> Feedback[Retour au joueur]
    Feedback --> Modify[Modifier le personnage]
    Modify --> Submit
```

### Étapes détaillées

1. **Création initiale**
   - Le joueur crée un nouveau personnage
   - Le personnage est créé en mode BROUILLON
   - 30 XP de départ sont attribués (20 XP Skills + 10 XP Gear)

2. **Configuration de base**
   - Nom du personnage
   - Choix de la faction (Nomads, Tech'ers, Rodoir, Néo-cuba)
   - Choix de la classe selon la faction
   - Rédaction du background

3. **Ajout de compétences**
   - Consultation du catalogue de compétences
   - Ajout de compétences avec coût en XP Skills
   - Vérification des prérequis (classe, faction, autres compétences)

4. **Ajout d'équipements**
   - Consultation du catalogue d'équipements
   - Ajout d'équipements avec coût en XP Gear
   - Vérification des prérequis

5. **Ajout d'assets**
   - Ajout d'assets spéciaux (capacités, objets)
   - Assets gratuits (coût 0 XP)

6. **Soumission**
   - Vérification des XP disponibles
   - Soumission pour validation
   - Statut passe à EN_COURS

7. **Validation par l'organisateur**
   - L'organisateur examine le personnage
   - Validation ou rejet
   - Si validé, définition du type (MAIN ou SECONDARY)

## Workflow de validation d'un personnage

Ce workflow décrit le processus de validation d'un personnage par un organisateur.

```mermaid
flowchart TD
    Start([Organisateur se connecte]) --> ViewList[Voir liste personnages EN_COURS]
    ViewList --> Filter{Filtrer par faction?}
    Filter -->|Oui| FilterFaction[Filtrer selon faction organisateur]
    Filter -->|Non| ViewAll[Voir tous les personnages]
    FilterFaction --> Select[Sélectionner un personnage]
    ViewAll --> Select
    Select --> Review[Examiner le personnage]
    Review --> CheckInfo{Vérifier informations}
    CheckInfo --> CheckSkills{Vérifier compétences}
    CheckSkills --> CheckGear{Vérifier équipements}
    CheckGear --> CheckXP{Vérifier XP}
    CheckXP --> Edit{Modifications nécessaires?}
    Edit -->|Oui| Modify[Modifier le personnage]
    Modify --> AddSkills[Ajouter compétences/équipements]
    AddSkills --> AddNotes[Ajouter notes organisateur]
    AddNotes --> Review
    Edit -->|Non| Decision{Valider ou rejeter?}
    Decision -->|Valider| Validate[Valider le personnage]
    Decision -->|Rejeter| Reject[Rejeter le personnage]
    Validate --> SetType[Définir type MAIN ou SECONDARY]
    SetType --> Notify[Notifier le joueur par email]
    Reject --> AddFeedback[Ajouter commentaires de rejet]
    AddFeedback --> Notify
    Notify --> End([Processus terminé])
```

### Étapes détaillées

1. **Consultation**
   - L'organisateur consulte la liste des personnages en validation
   - Filtrage possible par faction

2. **Examen**
   - Vérification des informations du personnage
   - Vérification des compétences et équipements
   - Vérification de la cohérence des XP

3. **Modifications éventuelles**
   - Ajout de compétences/équipements si nécessaire
   - Ajout de notes organisateur
   - Ajustement des XP si nécessaire

4. **Décision**
   - Validation : le personnage devient principal ou secondaire
   - Rejet : le personnage retourne au joueur avec commentaires

5. **Notification**
   - Email automatique au joueur
   - Information sur la validation ou le rejet

## Workflow d'inscription à un événement

Ce workflow décrit le processus d'inscription d'un joueur à un événement Dawn.

```mermaid
flowchart TD
    Start([Joueur se connecte]) --> ViewEvents[Consulter événements disponibles]
    ViewEvents --> CheckStatus{Vérifier statut événement}
    CheckStatus -->|Fermé| Closed[Événement fermé]
    CheckStatus -->|Ouvert| Select[Sélectionner événement]
    Closed --> End([Fin])
    Select --> CheckRegistration{Déjà inscrit?}
    CheckRegistration -->|Oui| AlreadyRegistered[Déjà inscrit]
    CheckRegistration -->|Non| Register[S'inscrire]
    AlreadyRegistered --> End
    Register --> EnterTicket[Entrer numéro ticket HelloAsso]
    EnterTicket --> Save[Enregistrer inscription]
    Save --> Confirmation[Confirmation inscription]
    Confirmation --> WaitEvent[Attendre l'événement]
    WaitEvent --> Participate[Participer à l'événement]
    Participate --> EventClosed{Événement terminé?}
    EventClosed -->|Non| WaitEvent
    EventClosed -->|Oui| GainXP[Gagner 3 XP automatiquement]
    GainXP --> UpdateCharacter[Mettre à jour personnage]
    UpdateCharacter --> End
```

### Étapes détaillées

1. **Consultation des événements**
   - Liste des événements Dawn disponibles
   - Statut des événements (ouvert, fermé, caché)

2. **Inscription**
   - Sélection d'un événement ouvert
   - Vérification de non-inscription précédente
   - Enregistrement du numéro de ticket HelloAsso

3. **Participation**
   - Participation à l'événement
   - Après la fin de l'événement, attribution automatique de 3 XP

4. **Mise à jour**
   - Les XP sont ajoutés au personnage principal
   - Disponibilité immédiate pour utilisation

## Workflow de gestion des utilisateurs (Admin)

Ce workflow décrit le processus de gestion des utilisateurs par un administrateur.

```mermaid
flowchart TD
    Start([Administrateur se connecte]) --> ViewUsers[Voir liste utilisateurs]
    ViewUsers --> Filter{Filtrer par rôle?}
    Filter -->|Oui| FilterRole[Filtrer par ROLE_USER/ORGA/ADMIN]
    Filter -->|Non| ViewAll[Voir tous les utilisateurs]
    FilterRole --> Action{Action?}
    ViewAll --> Action
    Action -->|Créer| Create[Créer nouvel utilisateur]
    Action -->|Modifier| Edit[Modifier utilisateur]
    Action -->|Supprimer| Delete[Supprimer utilisateur]
    Action -->|Inviter| Invite[Envoyer invitation]
    Create --> FillInfo[Remplir informations]
    FillInfo --> SetRole[Définir rôle]
    SetRole --> Save[Enregistrer]
    Edit --> ModifyInfo[Modifier informations]
    ModifyInfo --> ModifyRole[Modifier rôle si nécessaire]
    ModifyRole --> Save
    Delete --> Confirm{Confirmer suppression?}
    Confirm -->|Oui| Cascade[Supprimer avec cascade personnages]
    Confirm -->|Non| ViewUsers
    Cascade --> Save
    Invite --> EnterEmail[Entrer email]
    EnterEmail --> SendEmail[Envoyer email invitation]
    SendEmail --> Save
    Save --> End([Action terminée])
```

### Étapes détaillées

1. **Consultation**
   - Liste de tous les utilisateurs
   - Filtrage possible par rôle

2. **Création**
   - Création d'un nouvel utilisateur
   - Définition du rôle initial
   - Envoi d'invitation optionnel

3. **Modification**
   - Modification des informations utilisateur
   - Changement de rôle si nécessaire

4. **Suppression**
   - Suppression d'un utilisateur
   - Cascade sur les personnages associés

5. **Invitation**
   - Envoi d'invitation par email
   - Génération de lien d'invitation

## Navigation

- [Système de points d'expérience](05-systeme-xp.md)
- [Guide joueur](guides/guide-joueur.md)
- [Guide organisateur](guides/guide-organisateur.md)
