# Système de points d'expérience (XP)

Cette page détaille le fonctionnement du système de points d'expérience dans l'application Dawn GN.

## Vue d'ensemble

Le système de points d'expérience permet aux joueurs de développer leurs personnages en acquérant des compétences et des équipements. Les XP sont gagnés en participant aux événements Dawn et peuvent être dépensés pour améliorer les personnages.

## Calcul des XP

### XP de départ

Chaque nouveau personnage reçoit **30 XP de départ** :
- **20 XP Skills** : Points alloués aux compétences
- **10 XP Gear** : Points alloués aux équipements

### XP gagnés

Les joueurs gagnent des XP en participant aux événements Dawn :

- **3 XP par événement terminé** : Attribués automatiquement après la fin d'un événement
- Les XP sont ajoutés au personnage principal du joueur
- Seuls les événements avec le statut `CLOSED` génèrent des XP

### Calcul des XP disponibles

```
XP disponibles = XP gagnés - XP dépensés en compétences - XP dépensés en équipements + 30
```

Où :
- **XP gagnés** : Somme de 3 XP par événement Dawn terminé
- **XP dépensés en compétences** : Total des coûts des compétences apprises
- **XP dépensés en équipements** : Total des coûts des équipements possédés
- **30** : Bonus de départ

## Répartition des XP

### XP Skills

Les **XP Skills** sont utilisés pour acquérir des compétences :

- Chaque compétence a un coût de base selon sa rareté
- Le coût peut être modifié par l'organisateur lors de l'attribution
- Les compétences peuvent avoir des prérequis (classe, faction, autres compétences)

### XP Gear

Les **XP Gear** sont utilisés pour acquérir des équipements :

- Chaque équipement a un coût de base selon sa rareté
- Le coût peut être modifié par l'organisateur lors de l'attribution
- Les équipements peuvent avoir des prérequis

### Assets

Les **assets** (capacités spéciales, objets) sont **gratuits** :

- Coût : 0 XP
- Attribués par l'organisateur ou ajoutés par le joueur
- Pas de limitation par XP

## Utilisation des XP

### Ajout de compétences

1. Le joueur consulte le catalogue de compétences
2. Sélectionne une compétence disponible
3. Vérifie les prérequis (classe, faction, autres compétences)
4. Vérifie les XP Skills disponibles
5. Ajoute la compétence avec son coût
6. Les XP Skills sont déduits automatiquement

### Ajout d'équipements

1. Le joueur consulte le catalogue d'équipements
2. Sélectionne un équipement disponible
3. Vérifie les prérequis
4. Vérifie les XP Gear disponibles
5. Ajoute l'équipement avec son coût
6. Les XP Gear sont déduits automatiquement

### Vérification des XP disponibles

L'application calcule automatiquement :
- **XP disponibles totaux** : Total disponible pour le personnage
- **XP Skills disponibles** : XP Skills - coût des compétences apprises
- **XP Gear disponibles** : XP Gear - coût des équipements possédés

## Exemple de calcul

### Personnage nouvellement créé

- **XP de départ** : 30 (20 Skills + 10 Gear)
- **XP gagnés** : 0 (aucun événement)
- **XP disponibles** : 30

### Après participation à 3 événements

- **XP de départ** : 30
- **XP gagnés** : 9 (3 événements × 3 XP)
- **XP disponibles** : 39

### Après dépense de compétences et équipements

- **XP de départ** : 30
- **XP gagnés** : 9
- **Compétences apprises** : 15 XP dépensés
- **Équipements possédés** : 8 XP dépensés
- **XP Skills disponibles** : 20 - 15 = 5
- **XP Gear disponibles** : 10 - 8 = 2
- **XP disponibles totaux** : 30 + 9 - 15 - 8 = 16

## Rareté et coûts

### Compétences

Les compétences ont des coûts selon leur rareté :
- **Commune** : Coût faible
- **Rare** : Coût moyen
- **Épique** : Coût élevé
- **Légendaire** : Coût très élevé

### Équipements

Les équipements suivent la même logique de rareté :
- Le coût de base est défini selon la rareté
- L'organisateur peut modifier le coût lors de l'attribution

## Gestion par l'organisateur

L'organisateur peut :
- **Ajuster les XP** : Modifier les XP Skills ou Gear d'un personnage
- **Modifier les coûts** : Changer le coût d'une compétence ou équipement lors de l'ajout
- **Ajouter gratuitement** : Ajouter des compétences/équipements sans coût
- **Verrouiller** : Empêcher la modification d'éléments

## Points importants

1. **XP non transférables** : Les XP sont liés à un personnage spécifique
2. **Personnage principal** : Seul le personnage principal reçoit les XP des événements
3. **Vérification automatique** : L'application vérifie les XP disponibles avant chaque ajout
4. **Historique** : L'historique des dépenses est conservé

## Navigation

- [Workflows](04-workflows.md)
- [Guide joueur](guides/guide-joueur.md)
- [Structure des entités](06-entites.md)
