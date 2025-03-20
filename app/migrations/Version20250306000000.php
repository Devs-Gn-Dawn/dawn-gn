<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250306000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des compétences initiales';
    }

    public function up(Schema $schema): void
    {

        // Première vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Adapté à la radiation', 0, 'Les Nomads résistent mieux à la radiation !', 'Les Nomads résistent mieux à la radiation ! (Le tableau de radiation a été modifié pour votre faction, permettant d\'accumuler plus de radiations avec moins d\'effets néfastes). \"Plus ça brille, plus c\'est beau !\"', 'a:0:{}', 'a:1:{i:0;s:6:\"Nomads\";}', true),
            ('Commerce de proximité', 4, 'Chaque jour, vous pouvez acheter en ville jusqu\'à 2 choses à moitié prix', 'Chaque jour, vous pouvez acheter en ville jusqu\'à 2 choses à moitié prix (sauf armes et armures, non cumulables avec les compétences de marchandage). \"J\'peux pas, j\'ai soldes !\"', 'a:1:{i:0;s:7:\"Runners\";}', 'a:0:{}', true),
            ('Farfouiller', 4, 'Deux fois par jour, créez/réparez avec 1 scrap en moins', 'Deux fois par jour, vous pouvez créer ou réparer un objet en utilisant 1 scrap ou liquide en moins que nécessaire. \"A chaque fois que je le remonte, il me reste une vis en trop !\"', 'a:1:{i:0;s:7:\"Runners\";}', 'a:0:{}', true),
            ('Compagne', 4, 'Votre arme blanche inflige +1 dégât', 'Votre arme blanche reste votre meilleure amie : elle est connue par cœur et, en combat, votre arme de corps à corps de classe 1 inflige 2 dégâts au lieu d\'1. \"Ça c\'est mon couteau ! Il y en a beaucoup des comme ça, mais lui c\'est le mien !\"', 'a:1:{i:0;s:7:\"Raiders\";}', 'a:0:{}', true),
            ('Furie', 3, 'Résistez aux annonces \"peur\" et \"intimidation\" une fois par jour', 'Une fois par jour, vous résistez aux annonces \"peur\" et \"intimidation\" ce qui déclenche une furie vous obligeant à attaquer l\'opposant (au flingue ou à l\'arme blanche). \"PINAGE !\"', 'a:1:{i:0;s:7:\"Raiders\";}', 'a:0:{}', true),
            ('Absorption des radiations', 4, 'Absorbez la moitié des radiations d\'une cible', 'Après 5 minutes de méditation avec une cible irradiée tenue par les mains, le shaman absorbe la moitié des radiations (maximum 5, arrondi à l\'entier supérieur). \"Comme ça on me repère mieux la nuit.\"', 'a:1:{i:0;s:7:\"Shamans\";}', 'a:0:{}', true),
            ('Sens de la radioactivité', 3, 'Détectez les éléments fortement irradiés', 'Vous sentez les personnes et objets fortement irradiés (demandez un orga). \"Oh ça picote ici !\"', 'a:1:{i:0;s:7:\"Shamans\";}', 'a:0:{}', true),
            ('Habitué aux ombres', 0, 'Dissimulez une petite arme lors des fouilles', 'Permet de dissimuler une arme de lancer ou un couteau en cas de fouille (hors agonie). \"Personne ne m\'enlèvera mon couteau à pâté !\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Tech\'ers\";}', true)");

        // Deuxième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Paquetage militaire', 3, 'Trois balles gratuites chaque jour', 'Chaque jour, l\'état-major vous fournit trois balles (transformez 3 douilles en balles à minuit). \"Ainsi sonnèrent les douze douilles de minuit.\"', 'a:1:{i:0;s:10:\"Légionnaire\";}', 'a:0:{}', true),
            ('Précision militaire', 5, 'Une fois par combat, annoncez \"Perce-Armure\"', 'Votre discipline et entraînement militaire vous permettent de trouver la faille dans l\'armure de votre adversaire une fois par combat. Annoncez \"Perce-Armure\". \"Pas besoin d\'ouvre-boîte quand on a un couteau !\"', 'a:1:{i:0;s:10:\"Légionnaire\";}', 'a:0:{}', true),
            ('Couture à rivets', 3, 'Réparation rapide d\'armure avec un scrap', 'Vous pouvez bricoler une réparation d\'armure avec 1 scrap mécanique, mais celle-ci ne tiendra pas dans la durée et cédera au premier impact (non cumulable – tâche de 20 secondes). \"Ce soir je serai la plus belle pour aller frapper… !\"', 'a:1:{i:0;s:9:\"Façonneur\";}', 'a:0:{}', true),
            ('Réparation rapide', 3, 'Temps de réparation et craft réduit de moitié', 'Vous réduisez de moitié le temps de réparation et de craft sur machines en divers domaines. \"Si tu te passes de la noce de montage, tu gagnes du temps.\"', 'a:1:{i:0;s:9:\"Façonneur\";}', 'a:0:{}', true),
            ('Ingénieur', 6, 'Accès aux plans avancés de la NFT', 'En tant qu\'ingénieur, vous avez étudié les plans de rétro-ingénierie et guidez des artisans. Vous pouvez utiliser la NFT (Nouvelle Forge Technologique) pour accéder à des plans avancés. \"Tu connais l\'Ifone 1000? C\'est une RE-VO-LU-TION !\"', 'a:2:{i:0;s:9:\"Mécaniste\";i:1;s:13:\"Déchiffrement\";}', 'a:0:{}', true),
            ('Rétro-ingénierie', 4, 'Comprenez et retranscrivez les plans d\'un objet', 'Vous comprenez le fonctionnement d\'un objet en le démontant (ce qui le détruit) et pouvez retranscrire ou transmettre ses plans. \"Ça marche moins bien, oui, mais on la comprend vachement mieux maintenant !\"', 'a:2:{i:0;s:9:\"Mécaniste\";i:1;s:13:\"Déchiffrement\";}', 'a:0:{}', true)");

        // Troisième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Câble de symbiose biomécanique', 6, 'Connectez deux hommes rats pour +3 PV chacun', 'La technologie Homme-rat permet de \"connecter\" deux hommes rats, renforçant leur constitution (gain de 3 PV par personne) – voir règles spécifiques. \"C\'est ce qui s\'appelle être branché !\"', 'a:1:{i:0;s:19:\"Pilote (Homme-rat)\";}', 'a:0:{}', true),
            ('Réparation de fortune', 4, 'Réparation rapide mais temporaire', 'Avec 1 scrap mécanique, vous réparez immédiatement un système mécanique ou électrique simple, une arme, un flingue ou une armure – la réparation dure une demi-journée. \"Une agrafeuse, du scotch et des serre-joints et tu refais le monde !\"', 'a:1:{i:0;s:19:\"Le pouce (artisans)\";}', 'a:0:{}', true),
            ('Distillation', 5, 'Maîtrise de l\'alambic', 'Vous avez appris à faire fonctionner un alambic et à tester goût et effets selon les composants utilisés. \"Dites-moi, comment faites-vous pour faire passer le crapaud à l\'intérieur de la bouteille ?\"', 'a:1:{i:0;s:24:\"L\'index (scientifiques)\";}', 'a:0:{}', true),
            ('Herboriste', 2, 'Connaissance des plantes communes', 'Grâce à de longues années d\'exploration souterraine, vous connaissez la liste des plantes communes et leurs effets sans traitement. \"Vous prendrez bien des racines pour accompagner vos ores ?\"', 'a:1:{i:0;s:24:\"L\'index (scientifiques)\";}', 'a:0:{}', true),
            ('Évacuation', 3, 'Déplacez un allié inconscient', 'Habitué à évacuer des camarades blessés, vous pouvez, seul, faire marcher un compagnon inconscient sur quelques dizaines de mètres. \"- Dis à ma femme que… - Tu lui diras toi-même !\"', 'a:1:{i:0;s:19:\"Le majeur (soldats)\";}', 'a:0:{}', true)");

        // Quatrième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Mineurs', 4, 'Récupérez des ressources minérales', 'Spécialiste de l\'agrandissement de souterrains, vous pouvez miner une fois par demi-journée pour récupérer 2 ressources minérales. \"Un diamant ! Ah… non… juste un reste de bouteille de bière\"', 'a:1:{i:0;s:24:\"L\'annulaire (collecteurs)\";}', 'a:0:{}', true),
            ('Cueillette', 1, 'Obtenez des ressources végétales', 'Vous obtenez 3 ressources végétales à chaque début d\'opus grâce à vos explorations dans les souterrains et sous-bois. \"Vous prendrez bien des racines pour accompagner vos ores ?\"', 'a:1:{i:0;s:24:\"L\'annulaire (collecteurs)\";}', 'a:0:{}', true),
            ('Management', 5, 'Réduisez le temps de travail de moitié', 'Votre expérience en organisation de communauté vous permet de réduire de moitié le temps de travail physique en accompagnant quelqu\'un. \"Je te dis que sans moi tu sais même pas faire tes lacets !\"', 'a:1:{i:0;s:27:\"L\'auriculaire (Politiciens)\";}', 'a:0:{}', true),
            ('Négociateur', 3, 'Bonus de 30% aux ventes/achats', 'Votre verbe réputé vous confère un bonus de 30% aux ventes/achats auprès des marchands généraux (non cumulable avec d\'autres compétences de marchandage). \"On coupe la poire en deux ?\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Néo-cuba\";}', true),
            ('Confiant', 3, 'Immunité mentale en combat', 'Dans Néo-Cuba, en combat, vous devenez mentalement inébranlable et insensible à Torture, S\'imposer, Crainte et Peur. \"Totale maîtrise. Je suis fort.\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Néo-cuba\";}', true),
            ('Vieux singe', 3, 'Expert en fouille', 'Votre grande expérience vous permet de repérer tout lors de fouilles aux portes de Néo-Cuba (annule Cachotier et Habitué aux ombres). \"Oulah, c\'est quoi sous ton aisselle là ?\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Néo-cuba\";}', true),
            ('Rage au ventre', 3, 'Cassez un membre une fois par combat', 'Permet de casser un membre par une touche au Corps à Corps une fois par combat. \"T\'es chez moi gros naze !\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Néo-cuba\";}', true),
            ('Caché en ville', 5, 'Devenez invisible en ville', 'Dans la ville, près d\'une structure ou dans l\'ombre, en croisant les bras, vous devenez invisible (sauf en cas de conversation ou groupe). \"Hihi, plus là !\"', 'a:0:{}', 'a:1:{i:0;s:8:\"Néo-cuba\";}', true)");

        // Cinquième vague d'insertions (compétences générales)
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Addict aux pilules', 6, 'Immunité aux effets négatifs des drogues', 'Les effets néfastes des drogues et poisons (sauf antidotes et soins) ne vous atteignent pas. \"La cocaïne ça te fait décoller mais le pétard ça te fait continuer à planer, mec. C\'est ça mon secret pour pas redescendre.\"', 'a:0:{}', 'a:0:{}', true),
            ('Armurier', 5, 'Réparez et fabriquez des armes', 'Permet de réparer des armes de tir endommagées en 20 minutes ou de fabriquer des armes simples. \"Elle tire un peu, beaucoup, passionnément, à la folie...\"', 'a:0:{}', 'a:0:{}', true),
            ('Artificier', 3, 'Créez des munitions et explosifs', 'Vous créez des munitions à partir de douilles et de poudre et fabriquez des explosifs (nécessite un plan). \"Parce qu\'il vaut mieux l\'avoir entre les mains qu\'entre les yeux.\"', 'a:0:{}', 'a:0:{}', true),
            ('Assommer', 2, 'Assommez une cible pour 5 minutes', 'Vous portez un coup assommant avec un objet contondant; la cible reste 5 minutes KO si non aidée. \"Il racontera moins de conneries !\"', 'a:0:{}', 'a:0:{}', true),
            ('Autopsie', 4, 'Examinez les cadavres', 'Après 10 minutes d\'examen sur un cadavre, vous déterminez cause, heure de la mort et récupérez 2 liquides bio. \"Lire l\'avenir dans les osselets ? Moi je lis le passé dans les tripes !\"', 'a:0:{}', 'a:0:{}', true)");

        // Ajout des relations de pré-requis
        $this->addSql("INSERT INTO skill_requirements (skill_id, required_skill_id) 
            SELECT s1.id, s2.id 
            FROM skill s1, skill s2 
            WHERE (s1.label = 'Constitution 2' AND s2.label = 'Constitution 1')
            OR (s1.label = 'Constitution 3' AND s2.label = 'Constitution 2')
            OR (s1.label = 'Artificier' AND s2.label = 'Armurier')
            OR (s1.label = 'Autopsie' AND s2.label = 'Médecine')
            OR (s1.label = 'Bump et Baffe' AND s2.label = 'Bump')");

        // Sixième vague d'insertions (compétences générales suite)
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Bases de marchandage', 3, 'Bonus de 10% aux ventes/achats', 'Vous bénéficiez d\'un bonus de 10% aux ventes/achats auprès des marchands généraux. \"Vous avez pris en compte mon avantage fidélité ?\"', 'a:0:{}', 'a:0:{}', true),
            ('Bidouilleur', 4, 'Réparez des objets électriques basiques', 'Vous réparez, améliorez et fabriquez des objets électriques basiques à partir de scraps. \"Qu\'est ce qui se passe si je coupe le fil rouge ?\"', 'a:0:{}', 'a:0:{}', true),
            ('Bonimenteur charismatique', 4, 'Empêchez toute hostilité par la discussion', 'Hors combat, vous pouvez engager une discussion soutenue empêchant toute hostilité. \"C\'est trois nains, ils vont creuser à la mine…\"', 'a:0:{}', 'a:0:{}', true),
            ('Bonne digestion', 4, 'Consommez de la nourriture avariée', 'Une fois par jour, vous pouvez consommer une nourriture avariée, empoisonnée ou droguée (hors irradiés ou cannibalisme). \"Qu\'en est-il du deuxième petit-déjeuner ?\"', 'a:0:{}', 'a:0:{}', true),
            ('Boris pare-balle', 4, 'Une dernière action en agonie', 'Permet un dernier coup d\'éclat après être tombé en agonie (un tir ou une attaque dans les 20 secondes suivant l\'effondrement). \"Si vous me permettez une dernière bafouille…\"', 'a:0:{}', 'a:0:{}', true)");

        // Septième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Bourreau', 6, 'Interrogez efficacement', 'Après 5 minutes ou plus de torture, la cible doit répondre par oui ou non à 3 questions sans pouvoir mentir. \"Nous afons les moyens de fous faire parler !\"', 'a:0:{}', 'a:0:{}', true),
            ('Bricoleur', 4, 'Réparez des objets mécaniques basiques', 'Vous réparez, améliorez et fabriquez des objets mécaniques basiques à partir de scraps. \"Un vieux grille-pain et j\'te refais une bécane rutilante.\"', 'a:0:{}', 'a:0:{}', true),
            ('Bump', 4, 'Repoussez un ennemi', 'Permet d\'effectuer une charge par combat, repoussant l\'ennemi de 3 pas. \"Un pas en avant et trois pas en arrière... !\"', 'a:0:{}', 'a:0:{}', true),
            ('Bump et Baffe', 3, 'Envoyez un ennemi au sol', 'Charge qui envoie un ennemi au sol lors d\'un combat. \"... c\'est la politique du bon massacrant !\"', 'a:0:{}', 'a:0:{}', true),
            ('Cachotier', 4, 'Dissimulez un petit objet', 'Permet de dissimuler une bourse ou un petit objet (taille max : un œuf de poule) de manière indétectable. \"Cette montre, Butch, j\'ai réussi à la cacher 2 années durant…\"', 'a:0:{}', 'a:0:{}', true)");

        // Huitième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Calcification excessive', 3, 'Os incassables', 'Vos os deviennent incassables. \"Je suis toujours dur dans les bonnes occasions.\"', 'a:0:{}', 'a:0:{}', true),
            ('Calligraphe', 4, 'Rédigez des documents certifiés', 'Vous rédigez et certifiez divers documents (vrais ou faux) à l\'aide d\'un code de 16 caractères. \"Puisque je vous dis que c\'est un faux !\"', 'a:0:{}', 'a:0:{}', true),
            ('Cambriolage', 5, 'Volez des objets marqués', 'Permet de voler des objets marqués \"vol\". \"- Salut à vous, belle compagnie. Vous m\'attendiez ? - Tu viens pour le donjon ? - Certes, je suis le Voleur.\"', 'a:0:{}', 'a:0:{}', true),
            ('Chimie pratique', 4, 'Créez des pilules et médicaments', 'Vous créez des pilules ou médicaments à partir de produits chimiques (liste fournie en jeu). \"I\'m not in danger Skyler, I AM THE DANGER !\"', 'a:0:{}', 'a:0:{}', true),
            ('Chirurgie', 5, 'Soignez efficacement', 'Permet de soigner 1PV par 5 minutes et de traiter une fracture (le patient doit être couché). \"Te charcuter à l\'intérieur, pour te sentir bien à l\'extérieur !\"', 'a:0:{}', 'a:0:{}', true)");

        // Neuvième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Compagnon marchand', 3, 'Bonus de 20% aux ventes/achats', 'Vous montez d\'un grade dans le marchandage et bénéficiez d\'un bonus de 20% (non cumulable avec Bases de marchandage). \"Parce je ne suis pas assujetti à la TVA.\"', 'a:0:{}', 'a:0:{}', true),
            ('Constitution 1', 3, '+1 PV', 'Ajoute 1PV à votre total. \"Se prendre des baffes, ça me connait.\"', 'a:0:{}', 'a:0:{}', true),
            ('Constitution 2', 3, '+1 PV', 'Ajoute 1PV à votre total. \"Mange du pain, ça te fera du bien.\"', 'a:0:{}', 'a:0:{}', true),
            ('Constitution 3', 3, '+1 PV', 'Ajoute 1PV à votre total. \"Tu reprendras bien un peu de sel ?\"', 'a:0:{}', 'a:0:{}', true),
            ('Contorsionniste', 3, 'Échappez-vous facilement', 'Permet de se libérer de liens ou menottes ou de s\'extraire d\'un espace confiné après 3 minutes. \"Excusez-moi, je me suis permis de sortir. C\'est que je suis un peu claustrophobe vous voyez…\"', 'a:0:{}', 'a:0:{}', true)");

        // Dixième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Déchiffrement', 4, 'Lecture et écriture', 'Maîtrise de la lecture et de l\'écriture. \"C\'est marrant ce panneau -Goudron, ne pas se jeter dedans- au milieu du chemin.\"', 'a:0:{}', 'a:0:{}', true),
            ('Désert-medik', 2, 'Stabilisez un patient', 'Permet de stabiliser un patient pendant le soin. \"Si tu meurs, je t\'en mets une !\"', 'a:0:{}', 'a:0:{}', true),
            ('Désosseur', 5, 'Cassez un membre', 'Permet de casser un membre par une touche au Corps à Corps une fois par combat. \"Mes amants me disent tous que je suis craquant.\"', 'a:0:{}', 'a:0:{}', true),
            ('Destructeur', 5, 'Détruisez l\'équipement', 'Permet de porter un coup qui met hors d\'usage une armure, un bouclier ou une arme (une fois par combat). \"Bah maintenant elle va marcher beaucoup moins bien, forcément !\"', 'a:0:{}', 'a:0:{}', true),
            ('Dwain par-choc', 4, 'Résistez aux charges', 'Permet de résister à une charge qui vous repousse ou vous fait tomber. \"Avec mon nouveau régime, je tiens ma ligne.\"', 'a:0:{}', 'a:0:{}', true)");

        // Onzième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Dessinateur industriel', 3, 'Dessinez des plans', 'Vous dessinez des plans de fabrication d\'objets simples ou complexes et devez être présent pour la validation. \"il est pas beau mon carré ?!\"', 'a:0:{}', 'a:0:{}', true),
            ('Électricité', 4, 'Expert en électricité', 'Vous réparez, améliorez et fabriquez des objets électriques complexes à partir de scraps simples. \"Quand le courant passe… le courant passe.\"', 'a:0:{}', 'a:0:{}', true),
            ('Expert marchand', 3, 'Bonus de 30% aux ventes/achats', 'Vous trouvez les mots pour convaincre et montez d\'un grade dans le marchandage (-30% au shop). \"Comme je m\'ennuyais, j\'ai créé une holding.\"', 'a:0:{}', 'a:0:{}', true),
            ('Ferrailleur', 5, 'Récupérez des matériaux', 'Lors d\'une fouille sur un joueur ou PNJ en agonie, vous récupérez des matériaux provenant de ses armes et boucliers. \"Hey mais c\'est du bon acier ça !\"', 'a:0:{}', 'a:0:{}', true),
            ('Forgeron', 5, 'Forgez des armes', 'Permet de réparer et fabriquer flèches, armes de corps à corps et armures communes (réparation effective en 10 minutes). \"Et tu tapes tapes tapes, c\'est ta façon de forger !\"', 'a:0:{}', 'a:0:{}', true)");

        // Douzième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Hurlement tonitruant', 4, 'Faites désarmer un ennemi', 'En lançant un cri puissant, une cible désarme (une fois par combat). \"AAAhhhh, t\'as l\'air moins confiant d\'un coup !\"', 'a:0:{}', 'a:0:{}', true),
            ('Hypnotiseur', 7, 'Hypnotisez une personne', 'Toutes les deux heures, vous pouvez hypnotiser une personne (30 secondes minimum, hors combat). \"Aie confiaaaaannce...\"', 'a:0:{}', 'a:0:{}', true),
            ('Iron-head', 3, 'Résistez aux coups assommants', 'Permet de résister à un coup assommant. \"J\'entends vos arguments, mais je m\'en tape.\"', 'a:0:{}', 'a:0:{}', true),
            ('Lire et écrire l\'Anglais', 3, 'Maîtrisez l\'anglais', 'Vous parlez, lisez et écrivez l\'anglais. \"Bryan is in the kitchen ? Hey, rapporte moi une bière Bryan !\"', 'a:0:{}', 'a:0:{}', true),
            ('Maîtrise zen', 6, 'Résistez à tout une fois', 'Vous résistez à TOUT et pouvez annoncer \"Résiste\" une fois par GN. \"Un jour, le grand sage Lao Tseu a dit…\"', 'a:0:{}', 'a:0:{}', true)");

        // Treizième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Menaçant', 3, 'Inspirez la crainte', 'Votre apparence et vos mots inspirent la crainte une fois par discussion. \"Brian ta vraiment une sale tronche à faire peur\"', 'a:0:{}', 'a:0:{}', true),
            ('Mécanique', 4, 'Expert en mécanique', 'Vous réparez, améliorez et fabriquez des objets mécaniques complexes à partir de systèmes simples et d\'un plan. \"Un jour, j\'aurai un robot pour bosser à ma place. D\'ici là, faut le fabriquer.\"', 'a:0:{}', 'a:0:{}', true),
            ('Médecine', 4, 'Soignez les blessures', 'Permet de soigner un patient pour 1PV en au moins 5 minutes (avec trousse et bandage). \"Je vous préviens, je vous facture le pressing pour le sang que vous avez foutu sur ma blouse !\"', 'a:0:{}', 'a:0:{}', true),
            ('Mutation régénératrice', 8, 'Régénérez naturellement', 'Vous régénérez 1PV toutes les 2 heures grâce à une mutation de naissance. \"Un mars et ça repart !\"', 'a:0:{}', 'a:0:{}', true),
            ('Pharmacologie', 4, 'Connaissez les médicaments', 'Vous connaissez les effets des médicaments et savez les reconnaître. \"J\'ai un très bon générique moitié prix à la place.\"', 'a:0:{}', 'a:0:{}', true)");

        // Quatorzième vague d'insertions
        $this->addSql("INSERT INTO skill (label, base_cost, short, description, required_classes, required_factions, visibility) VALUES
            ('Pickpocket', 5, 'Volez des objets précieux', 'Vous subtilisez des objets précieux en accrochant une pince à linge sur la bourse (max 3 à la fois). \"Ce qui est à toi est à moi. Ce qui est à moi, reste à moi.\"', 'a:0:{}', 'a:0:{}', true),
            ('Premiers secours', 3, 'Stabilisez à 1PV', 'Permet de stabiliser un patient à 1PV en 5 minutes à l\'aide d\'une trousse et d\'un bandage. \"Avant tu avais une hémorragie, mais ça c\'était avant.\"', 'a:0:{}', 'a:0:{}', true),
            ('Résistant à la douleur', 3, 'Continuez à bouger malgré les blessures', 'Vous pouvez courir même à 1PV et ramper sur 10–20 m même à 0PV. \"Fuir d\'abord, avoir mal après !\"', 'a:0:{}', 'a:0:{}', true),
            ('Recycleur', 5, 'Échangez des ressources', 'Vous pouvez recycler des ressources deux fois par jour pour échanger gratuitement une ressource contre une autre. \"Au pire on arrivera bien à caler une table avec.\"', 'a:0:{}', 'a:0:{}', true),
            ('Sans peur', 6, 'Immunité totale à la peur', 'Vous restez insensible aux menaces, à la torture et à la peur. \"Ce n\'est pas que j\'ai raison tout le temps mais plutôt que je n\'ai jamais tort.\"', 'a:0:{}', 'a:0:{}', true),
            ('S\'imposer', 5, 'Faites fuir un joueur', 'Hors combat, vous menacez un joueur qui doit alors fuir. \"J\'ai dit quelque chose qu\'il ne fallait pas ?\"', 'a:0:{}', 'a:0:{}', true),
            ('Saboteur', 3, 'Sabotez discrètement', 'Discrètement et hors combat, vous mettez hors d\'usage une arme en y collant deux gommettes. \"Moi le matin je casse le vent, je fais chier les gens. ça me purifie, c\'est important.\"', 'a:0:{}', 'a:0:{}', true)");

        // Ajout des relations de pré-requis
        $this->addSql("INSERT INTO skill_requirements (skill_id, required_skill_id) 
            SELECT s1.id, s2.id 
            FROM skill s1, skill s2 
            WHERE (s1.label = 'Constitution 2' AND s2.label = 'Constitution 1')
            OR (s1.label = 'Constitution 3' AND s2.label = 'Constitution 2')
            OR (s1.label = 'Artificier' AND s2.label = 'Armurier')
            OR (s1.label = 'Autopsie' AND s2.label = 'Médecine')
            OR (s1.label = 'Bump et Baffe' AND s2.label = 'Bump')
            OR (s1.label = 'Calligraphe' AND s2.label = 'Déchiffrement')
            OR (s1.label = 'Chimie pratique' AND s2.label = 'Pharmacologie')
            OR (s1.label = 'Chirurgie' AND s2.label = 'Médecine')
            OR (s1.label = 'Compagnon marchand' AND s2.label = 'Bases de marchandage')
            OR (s1.label = 'Expert marchand' AND s2.label = 'Compagnon marchand')
            OR (s1.label = 'Électricité' AND s2.label = 'Bidouilleur')
            OR (s1.label = 'Mécanique' AND s2.label = 'Bricoleur')
            OR (s1.label = 'Médecine' AND s2.label = 'Premiers secours')
            OR (s1.label = 'Résistant à la douleur' AND s2.label = 'Iron-head')
            OR (s1.label = 'Saboteur' AND s2.label = 'Pickpocket')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM skill_requirements');
        $this->addSql('DELETE FROM skill');
    }
}
