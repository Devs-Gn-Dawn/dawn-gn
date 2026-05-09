# Fonctionnalités à développer (backlog)

Ce document détaille des évolutions produit souhaitées. Aucune implémentation n’est décrite ici : objectifs, périmètre et critères d’acceptation seulement.

**Décisions produit (validées)** : voir encadré en fin de document.

---

## 1. Changer le type d’un personnage (Principal / Reroll / Brouillon) — **livré**

**Contexte actuel (référence)** : un personnage a un `CharacterType` : `Main` (Principal), `Secondary` (Reroll), `Draft` (Brouillon). Les joueurs peuvent déjà promouvoir un brouillon vers principal ou reroll via le flux existant, avec des règles du type « un seul principal par compte », « un seul reroll », et uniquement tant que le personnage n’est pas dans un état de validation bloquant.

**Besoin** : permettre à **l’orga et l’admin** de changer le type **en dehors** du parcours joueur : corriger une erreur, reclasser un personnage, repasser en brouillon sans recréer la fiche.

**Réalisation (résumé)** :

- Endpoint `POST /orga/character/{id}/type` (JSON `type` : `Main`, `Secondary`, `Draft`) ; service `CharacterService::changeCharacterTypeForStaff` ; traçabilité via log applicatif structuré.
- **Transitions** : toutes les combinaisons pour le staff.
- **Un principal / un reroll par compte** : si la cible devient principal ou reroll alors qu’une autre fiche du même joueur occupe déjà ce rôle, l’autre fiche est **repassée en brouillon** automatiquement ; l’UI et la réponse JSON listent les fiches concernées.
- **Validation** : le changement de type staff **ne dépend pas** de `ValidationType` (possible même si la fiche est déjà validée, en validation, etc.).
- **Interface** : bloc « Type de fiche (orga) » sur la fiche orga (`/orga/character/{id}/edit`), confirmation avant envoi, messages d’erreur explicites côté API.
- **Rôles** : `ROLE_ADMIN` hérite de `ROLE_ORGA` dans la hiérarchie Symfony pour accéder au périmètre orga (dont cette action). Documentation : `docs/07-api.md`.

**Critères d’acceptation (brouillon)** — couverts par la livraison ci-dessus.

---

## 2. Changer le propriétaire d’une inscription (billet HelloAsso)

**Périmètre** : une **inscription** (`Registration`) = lien joueur + **événement** + **numéro de billet HelloAsso** (et métadonnées associées). Il ne s’agit pas de la création de compte (`/register`).

**Besoin** : pouvoir **réattribuer** une inscription d’un utilisateur A vers un utilisateur B (transfert de billet, correction de compte, etc.).

**À préciser** :

- Cas d’usage prioritaires : transfert entre joueurs, correction après mauvais compte, fusion de comptes ?
- Conserver l’historique (propriétaire initial, date, auteur de la réassignation) ?
- Effets de bord : fiches personnage, PDF, check-in, emails déjà envoyés.

**Critères d’acceptation (brouillon)** :

- Seuls les rôles autorisés peuvent réassigner ; confirmation explicite avant sauvegarde.
- Vérifications de cohérence (doublon d’inscription sur le même événement pour B, billet déjà utilisé, etc.).
- Message clair en cas d’échec ; succès visible côté fiches joueur / liste orga.

---

## 3. Débloquer un personnage (réouverture workflow de validation) — **livré**

**Besoin** : **rouvrir** la fiche côté **workflow de validation** — c’est-à-dire permettre à nouveau l’édition / le cycle de validation en ajustant l’état (`ValidationType`), et non pas le simple déverrouillage des lignes compétences / équipements / assets (`locked`).

**Réalisation (résumé)** :

- **États source** : `VALIDE`, `EN_COURS`, `REJETE` → cible **`NON_VALIDE`** (déjà `NON_VALIDE` → erreur explicite). Pas de changement de `CharacterType` ni des flags `locked`.
- **Service** : `CharacterService::reopenValidationWorkflowForStaff` ; log `character_validation_reopened_by_staff`.
- **API** : `POST /orga/character/{id}/reopen-validation` (corps JSON `{}` accepté) ; réponse `{ "success", "previousValidationType" }` ; e-mail joueur via `sendCharacterValidationEmail(..., 'reopened')` (texte neutre, distinct du **rejet** liste).
- **Rejet liste** : `POST /api/character/{id}/reject` inchangé (refus + mail « non validé »).
- **Interface** : bouton « Rouvrir le workflow » sur `/orga/character/{id}/edit` si la fiche est en validation, validée ou rejetée ; confirmation avant envoi.

**Critères d’acceptation (brouillon)** — couverts par la livraison ci-dessus.

---

## 4. Statistiques organisateur — **livré**

**Besoin** : indicateurs simples pour l’équipe orga, **pour l’opus en cours** (l’événement actuellement pertinent pour les inscriptions — en pratique l’événement « ouvert » côté configuration métier, ex. statut `open` dans la configuration des opus).

**Métriques (livraison)** :

1. **Personnes inscrites** à l’opus « ouvert » : **comptes distincts** (`COUNT(DISTINCT user)` sur `Registration`).
2. **Principaux validés** : fiches **Principal** (`CharacterType::MAIN`) au statut **validé**, dont le joueur a au moins une inscription sur l’opus concerné.

**Réalisation (résumé)** :

- **Opus en cours** : `EventType::getOpenEventTypes()` (statut `open` dans `EventType::EVENT_STATUS`) ; plusieurs opus ouverts = union des slugs dans les requêtes.
- **Personnes inscrites** : `RegistrationRepository::countDistinctUsersByEventSlugs` ; ventilation faction : `countDistinctUsersByEventSlugsGroupedByUserFaction` (clé = `User.faction`).
- **Principaux validés** : `CharacterRepository::countValidatedMainForUsersRegisteredToEvents` ; ventilation faction : `countValidatedMainForUsersRegisteredToEventsGroupedByCharacterFaction` (clé = `Character.faction`).
- **Interface** : tableau de bord `/orga` (`orga/index.html.twig`) — présentation alignée sur le tableau de bord admin (cartes Soft UI) ; libellé(s) d’opus, cartes indicateurs, **tableau par faction** (ligne « Non renseigné / autre » si besoin), accès rapide vers la gestion des personnages ; si aucun opus `open`, message explicite sans métriques.
- **Navigation** : après connexion, les comptes **ROLE_ORGA** (sans admin) et la visite de `/` une fois connecté·e sont redirigés vers `/orga` (`FormLoginSuccessHandler`, `HomeController`).

**Critères d’acceptation (brouillon)** — couverts par la livraison ci-dessus.

---

## 5. Changer la faction / la classe d’un personnage — **livré**

**Contexte actuel (référence)** : une fiche a une `faction` (chaîne) et une `class` (`ClassType`). Les compétences du référentiel portent des contraintes `required_factions` et `required_classes` : une compétence n’est « disponible » pour un personnage que si ces contraintes sont satisfaites (voir filtrage côté `SkillRepository::findAvailableSkillsForCharacter`). Les **assets** peuvent aussi porter des factions requises (`Asset::required_factions`).

**Besoin** : permettre de **changer la faction et la classe** conjointement (la classe doit appartenir à la faction cible, comme à la création de personnage).

**Réalisation (résumé)** :

- **API** : `POST /orga/character/{id}/faction-class/preview` (sans écriture) et `POST /orga/character/{id}/faction-class` ; corps JSON `{ "faction", "class" }` avec les valeurs d’enum (`FactionType.value`, `ClassType.value`). Documentation : `docs/07-api.md`.
- **Métier** : `CharacterService::previewFactionClassChangeForStaff`, `changeFactionAndClassForStaff`, `getSkillLearnedToRemoveForFactionClassChange` ; filtre d’éligibilité `SkillRepository::skillMatchesFactionAndClass` (aligné avec le référentiel) ; **retrait en cascade** des `SkillLearned` via les prérequis `Skill::requiredSkills` jusqu’à point fixe ; suppression y compris des lignes **verrouillées** (`locked`).
- **Cohérence faction / classe** : `assertClassBelongsToFaction` (`ClassType::getRequiredFaction()`).
- **XP** : **pas** de restitution des PA sur les compétences supprimées (décision produit pour cette livraison).
- **Possessions / équipements / assets** : **hors périmètre** v1 (pas de purge automatique ; avertissement dans l’UI orga).
- **Validation** : pas de blocage sur `ValidationType` pour le staff (comme le changement de type §1).
- **Interface** : bloc « Faction / classe (orga) » sur `/orga/character/{id}/edit` ; liste des classes via `GET /characters/api/classes/{faction}` ; prévisualisation puis confirmation avant enregistrement ; log structuré `character_faction_class_changed_by_staff`.

**Critères d’acceptation (brouillon)** — couverts par la livraison ci-dessus.

---

## Décisions produit (validées)

| Sujet | Décision |
|--------|----------|
| « Register » | Inscription liée au **billet HelloAsso** (`Registration`), pas la page d’inscription compte. |
| Débloquer | **Réouverture workflow** : `POST /orga/character/{id}/reopen-validation` → `NON_VALIDE` depuis `VALIDE` / `EN_COURS` / `REJETE` ; distinct du rejet liste et du `locked` ligne à ligne. |
| Changement de type | **Réservé orga / admin** uniquement. |
| Statistiques (v1) | **Inscrits distincts** + **principaux validés** (MAIN + VALIDE, joueur inscrit à l’opus `open`) sur `/orga`, avec **détail par faction** (profil joueur vs fiche) ; pas d’API JSON dédiée. |
| Connexion orga | **ROLE_ORGA** (sans `ROLE_ADMIN`) : redirection vers **`/orga`** après login et depuis `/` ; les admins restent envoyés vers **`/admin`**. |
| Faction / classe | Retrait des **compétences non éligibles** + **cascade prérequis**, **puis** mise à jour faction/classe ; **pas** de restitution d’XP ; **pas** de purge possessions/assets en v1 ; staff sans blocage `ValidationType`. |

---

## Questions encore ouvertes

1. **Réassignation de billet** : faut-il un **journal** obligatoire des transferts (audit) ?
2. **Changement faction/classe** : évolutions futures — **restituer l’XP** des compétences retirées ? **Purger** possessions / assets incompatibles ? (v1 livrée : non / non)

---

*Dernière mise à jour : §3 réouverture workflow validation (livré) ; §4 stats orga figées ; docs alignées.*
