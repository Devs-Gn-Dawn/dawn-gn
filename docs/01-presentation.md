# Présentation de l'application Dawn GN

## Qu'est-ce que Dawn GN ?

Dawn GN est une application web développée avec Symfony 7.2 pour la gestion complète d'un jeu de rôle grandeur nature (GN). Elle permet aux joueurs de créer et gérer leurs personnages, et aux organisateurs de valider et administrer les inscriptions aux événements.

## Contexte

L'application a été conçue pour faciliter la gestion d'un GN post-apocalyptique appelé "Dawn", où les joueurs incarnent des personnages appartenant à différentes factions dans un monde dévasté. Chaque joueur peut créer un ou plusieurs personnages, les équiper de compétences et d'équipements, et participer à des événements réguliers.

## Objectifs

### Pour les joueurs
- Créer et personnaliser leurs personnages facilement
- Gérer leurs compétences, équipements et possessions
- S'inscrire aux événements Dawn
- Suivre leur progression et leurs points d'expérience
- Générer leur fiche de personnage en PDF

### Pour les organisateurs
- Valider les personnages créés par les joueurs
- Gérer les personnages et leurs attributs
- Consulter un **tableau de bord** (`/orga`) avec indicateurs sur l’opus en cours (inscrits, principaux validés, ventilation par faction)
- Communiquer avec les joueurs
- Suivre les inscriptions aux événements

### Pour les administrateurs
- Gérer les utilisateurs et leurs rôles
- Envoyer des invitations aux nouveaux utilisateurs
- Administrer l'ensemble de l'application

## Public cible

L'application s'adresse à trois types d'utilisateurs :

1. **Joueurs** : Participants au GN qui créent et gèrent leurs personnages
2. **Organisateurs (Orga)** : Membres de l'équipe d'organisation qui valident les personnages et gèrent les événements
3. **Administrateurs** : Gestionnaires de l'application avec accès complet

## Fonctionnalités principales

### Gestion de personnages
- Création de personnages avec choix de faction et classe
- Système de compétences avec points d'expérience
- Gestion d'équipements et possessions
- Assets spéciaux (capacités, objets)
- Rédaction de background
- Validation par les organisateurs

### Gestion des événements
- Inscription aux sessions Dawn (Dawn 31-40)
- Suivi des inscriptions avec tickets HelloAsso
- Attribution automatique de points d'expérience après participation

### Administration
- Gestion des utilisateurs et rôles
- Validation des personnages
- Communication avec les joueurs
- Génération de fiches de personnage en PDF

## Factions du jeu

L'univers de Dawn comprend quatre factions principales :

- **Nomads** : Nomades survivants du désert
- **Tech'ers** : Technologues et mécaniciens
- **Rodoir** : Société organisée en castes (les doigts)
- **Néo-cuba** : Nouvelle société cubaine

Chaque faction propose différentes classes de personnages avec leurs spécificités.

## Système de progression

Les joueurs gagnent des points d'expérience (XP) en participant aux événements Dawn :
- 3 XP par événement terminé
- 30 XP de départ pour chaque nouveau personnage
- Répartition entre XP Skills (compétences) et XP Gear (équipements)

Pour plus de détails, consultez la [documentation du système XP](05-systeme-xp.md).

## Navigation

- [Fonctionnalités détaillées](02-fonctionnalites.md)
- [Architecture technique](03-architecture.md)
- [Guides d'utilisation](guides/guide-joueur.md)
