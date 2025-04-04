<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250320000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table gear et ajout des équipements initiaux';
    }

    public function up(Schema $schema): void
    {


        // Insertion des armes de corps à corps
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Arme de corps à corps Classe 1', 1, 'Vous avez une arme de taille inférieure/égale à 50 cm. \"Si ça coupe, ça coupe...\"', 'Arme de taille inférieure/égale à 50 cm', true),
            ('Arme de corps à corps Classe 2', 3, 'Vous avez une arme de taille inférieure/égale à 110 cm. \"30 cm de ferraille en plus entre toi et ton voisin, ça compte.\"', 'Arme de taille inférieure/égale à 110 cm', true),
            ('Arme de corps à corps Classe 3', 5, 'Vous avez une arme de taille supérieure à 110 cm. \"Avoir une lance ou un gros taptap, c\'est cool, comme ça tu sais ce que tu fais avec tes deux mains !\"', 'Arme de taille supérieure à 110 cm', true),
            ('Armes de jet', 1, 'Vous avez 1 arme de jet ramassable. Cumulable. \"Parce qu\'un couteau ça n\'a pas besoin de munitions. Faut juste les retrouver ensuite !\"', '1 arme de jet ramassable (cumulable)', true),
            ('Arme de trait', 3, 'Vous avez un arc ou une arbalète de 25 lbs maximum (arme seule, pas de munition). Il faut avoir la compétence Forgeron pour fabriquer les projectiles. Précision pour la sarbacane : le projectile est à usage unique, on ne peut pas le récupérer.', 'Arc ou arbalète de 25 lbs maximum', true)");

        // Insertion des armes à distance
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Arme de Classe 1', 2, 'Vous avez une arme de tir mono coup (arme seule, pas de munition). \"J\'ai appris à utiliser cette antiquité, le plus important c\'est que je l\'ai conservée.\"', 'Arme de tir mono coup', true),
            ('Arme de Classe 2', 4, 'Vous avez une arme qui offre la possibilité de tirer plusieurs munitions à la suite ou en même temps (arme seule, pas de munition). \"Y a plein de choses intéressantes dans le monde, comme mon Colt 45. Je l\'aime bien mon Colt.\"', 'Arme multi-coups', true),
            ('Arme de Classe 3', 8, 'Vous avez une arme mortelle, perfectionnée à l\'extrême (semi-automatique ou automatique avec moteur. Arme seule, pas de munition). \"Certains aiment les jouets, moi j\'aime les gros.\"', 'Arme semi-auto ou automatique', true)");

        // Insertion des boucliers et munitions
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Bouclier léger', 3, 'Vous avez un bouclier résistant aux armes de corps à corps. \"Oui c\'est un morceau de table de cuisine, mais bon ça protège.\"', 'Bouclier résistant au corps à corps', true),
            ('Bouclier lourd', 6, 'Vous avez un bouclier résistant aux armes de corps à corps et aux tirs. \"Un bouclier anti émeute, votre meilleure assurance vie.\"', 'Bouclier résistant au corps à corps et tirs', true),
            ('Kit de munition', 1, '1 munition prête à l\'emploi dès le début de partie. (Cumulable 5 fois). \"Y a ceux qui ont les munitions, et ceux qui n\'ont pas de munitions. J\'ai des munitions. Je ne creuserai pas pour toi.\"', '1 munition (cumulable 5 fois)', true)");

        // Insertion des armures
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Kit d\'armure légère', 2, 'Une fois par combat, bloque le premier coup ou tir reçu. \"C\'est pas forcément grand-chose, mais au moins ça permet de limiter les dégâts pendant un temps.\"', 'Bloque 1 coup par combat', true),
            ('Kit d\'armure médium', 4, 'Une fois par combat, bloque les 2 premiers coups ou tirs reçus. \"De quoi se sentir en sécurité, même si vos compagnons vous tirent dans le dos.\"', 'Bloque 2 coups par combat', true),
            ('Kit d\'armure lourde', 6, 'Une fois par combat, bloque les 3 premiers coups ou tirs reçus. \"Même pas peur, c\'est pas une ou deux balles là-dedans qui vous laissera pour mort !\"', 'Bloque 3 coups par combat', true)");

        // Insertion des équipements divers
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Boite à rabiot', 4, 'Vous obtenez de manière aléatoire 3 ressources du jeu de type mécanique, électrique ou chimique (faites la demande lors du check orga à l\'entrée sur site). \"Je ramasse, je stocke, je garde, ça peut toujours servir...\"', '3 ressources aléatoires', true),
            ('Gemme du DAWN', 2, 'Les gemmes du DAWN sont devenues les seules sources d\'énergies viables, tout objet nécessitant de l\'électricité en a besoin pour assurer son fonctionnement. \"Incarnation du Dieu élec, prêtez-nous votre lumière !\"', 'Source d\'énergie pour objets électriques', true),
            ('Gemmes du DAWN (Stock)', 4, '3 gemmes du DAWN pour faire fonctionner vos objets électriques et autres lampes. \"J\'ai peut-être moins d\'armes, mais moi je vois où je mets les pieds la nuit.\"', '3 gemmes du DAWN', true),
            ('Med-kit', 3, 'Permet de diviser par deux les temps de soin. Peut servir 2 fois. \"Savoir soigner c\'est bien, avoir le matos, c\'est mieux.\"', 'Divise par 2 les temps de soin (2 utilisations)', true),
            ('Quelques billets', 4, 'Vous commencez avec 600 sols. \"Parce qu\'être un fils de bourge, bah ça aide un peu dans la vie.\"', '600 sols de départ', true),
            ('Sérum de régénération', 3, 'Chaque dose injectée vous remet 1 PV (utilisable avec la compétence Premier secours, Médecine ou Chirurgien). Vous avez 2 doses par seringue, vous avez une seringue. \"Nos ancêtres ont trouvé un moyen hyper rapide pour se soigner.\"', 'Seringue avec 2 doses de +1 PV', true)");

        // Insertion des drogues et médicaments
        $this->addSql("INSERT INTO gear (label, base_cost, description, short, visibility) VALUES
            ('Amphétamine', 5, '1 pilule. Une fois à l\'agonie, vous trouvez la force de rentrer à votre camp pour y finir vos jours, vous ne pouvez pas courir ni vous battre, vous semblez blessé dans votre démarche. \"Vous connaissez les zombies? La légende vient de là...\"', 'Permet de rentrer au camp en agonie', true),
            ('Anti-poi', 3, 'Une pilule qui nettoie et soulage l\'organisme. Arrête les effets des poisons communs, des champignons et des drogues. \"Vite, vite trouve moi l\'antidote\"', 'Annule les effets des poisons', true),
            ('Anti-Rad', 3, '2 pilules faisant chacune baisser de 2 vos niveaux de radiations. \"Le seul vrai remède pour ne pas finir avec le cerveau en yaourt.\"', '2 pilules de -2 radiations', true),
            ('Cocaine', 3, '1 dose de poudre qui permet d\'ignorer les blessures pendant 20 minutes et de couper momentanément les effets de la faim pour 6 heures. Au bout de 20 minutes, vous subissez les dégâts ignorés. Après 6h, crise de manque et autres symptômes apparaissent. \"John, tu m\'as l\'air pâle, ça va ?\"', 'Ignore blessures (20min) et faim (6h)', true),
            ('Cyanure', 3, '3 pilules diluables, chacune infligeant 2 dégâts, 1 minute après ingestion. Les effets peuvent être combinés. \"Et si vous invitiez vos ennemis à prendre l\'apéro ?\"', '3 pilules de poison (2 dégâts)', true),
            ('Frénépil', 3, '1 pilule qui permet d\'ignorer les coups, blessures et fractures le temps d\'un combat. Une fois le combat terminé, vous tombez en agonie, avec les fractures subies appliquées. Vous apparaissez frénétique. \"Je suis invulnérable, personne ne m\'arrête !\"', 'Ignore dégâts pendant 1 combat', true),
            ('Héroïne', 4, '1 dose de poudre provoquant un gros \"High\" et la perte de 1 PV par demi-journée sans nouvelle dose. Crise de manque dès 6h sans dose. \"Ban… l\'étoffe des héros !\"', 'High puissant avec dépendance', true),
            ('Kétamine', 5, '2 pilules de somnifères (5 minutes). \"Un bon vieux tranquillisant pour poney, de quoi faire une bonne sieste..\"', '2 pilules de sommeil (5min)', true),
            ('LSD', 3, '2 pilules donnant 10 minutes d\'hallucinations diverses (prévenez un orga). \"Vous voulez voyager, c\'est départ immédiat ! Prochain arrêt, le pays des chats qui fument !\"', '2 pilules d\'hallucinations (10min)', true),
            ('Scopolamine', 4, '2 pilules de sérum de vérité. Votre \"testeur\" devient plus coopératif et ne peut vous mentir durant les 3 minutes suivantes (annule les compétences \"sans peur\" et \"confiant\"). \"La vérité c\'est quelque chose de rare, j\'en ai jamais trouvé assez pour remplir la moitié d\'un verre...\"', '2 pilules de sérum de vérité (3min)', true),
            ('Stéroïde', 4, '3 pilules donnant +1 dégât lors du prochain coup au corps à corps. Non cumulable en une seule prise. \"Je me sens FORT, je suis FORT !\"', '3 pilules de +1 dégât au CàC', true)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gear');
    }
}
