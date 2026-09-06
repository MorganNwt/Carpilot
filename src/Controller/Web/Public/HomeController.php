<?php

namespace App\Controller\Web\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function DataCollection()
    {
        $steps = [
            [
                'icon' => 'fas fa-mouse-pointer',
                'image' => 'Clics.png',
                'title' => 'En quelques clics',
                'text' => "Choisir CarPilot, c’est dire adieu aux tracas de la revente d’occasion !",
                'car_image' => null
            ],
            [
                'icon' => 'fas fa-university',
                'image' => 'Virement.png',
                'title' => 'Virement instantané sur votre compte',
                'text' => "Une fois le véhicule déposé, vous recevez votre argent instantanément sur votre compte.",
                'car_image' => null
            ],
            [
                'icon' => 'fas fa-users',
                'image' => 'Particulier et pro.png',
                'title' => 'Pour particuliers et professionnels',
                'text' => "Nous rachetons immédiatement votre véhicule, que vous soyez particulier ou professionnel.",
                'car_image' => null
            ],
            [
                'icon' => 'fas fa-car',
                'image' => 'Véhicule.png',
                'title' => 'Tous types de véhicules',
                'text' => "Que vous ayez une citadine, un break ou un SUV, nous avons une offre pour vous !",
                'car_image' => 'renault_20clioeditiononehb5b_angularfront 1.png'
            ],
        ];

        $faqs = [
            [
                'question' => "Comment fonctionne le processus de rachat de véhicules sur votre site ?",
                'answer' => "
                    Commencez par réaliser une estimation en ligne de votre véhicule. 
                    En quelques minutes, vous pourrez renseigner toutes les informations de votre voiture et de son état. 
                    Vous recevrez alors une proposition de rachat sous 45 minutes. 
                    Prenez ensuite rendez-vous en agence pour faire contrôler l'état de votre véhicule avec un expert CarPilot. 
                    Vous obtiendrez l'offre définitive dès la fin de l'expertise. 
                    Si celle-ci vous convient, nous fixons ensemble un rendez-vous afin de finaliser la transaction. 
                    Nous nous occupons des démarches administratives et ainsi, dès le dépôt de votre véhicule, vous repartez avec votre règlement.
                "
            ],
            [
                'question' => "Quels types de véhicules acceptez-vous pour le rachat ?",
                'answer' => "
                    Nous acceptons toutes les voitures, peu importe leur marque. Votre véhicule doit néanmoins respecter les conditions suivantes :<br><br>
                    • Année de mise en circulation à partir de 2010<br>
                    • Moins de 150 000 km<br>
                    • Suivi mécanique à minima partiel<br>
                    • Non accidenté et roulant<br>
                    • Le vendeur doit être le propriétaire (carte grise obligatoire)<br><br>
                    Pour les véhicules hors de ces conditions, merci de prendre rendez-vous pour que nous puissions trouver la meilleure solution de vente.
                "
            ],
            [
                'question' => "Mon véhicule a-t-il besoin d'être en parfait état pour être éligible au rachat ?",
                'answer' => "
                    Non, nous acceptons également les véhicules endommagés. 
                    Cependant, l'état de votre véhicule peut influencer l'offre que nous pouvons vous faire.
                "
            ],
            [
                'question' => "Dois-je effectuer des réparations avant de vendre mon véhicule ?",
                'answer' => "
                    Non, vous n'avez pas besoin de faire de réparations. 
                    Nous considérons l'état actuel de votre véhicule lors de l'évaluation.
                "
            ],
            [
                'question' => "Comment se déroule le paiement une fois que j'ai accepté l'offre ?",
                'answer' => "
                    Le paiement s'effectue selon votre préférence : 
                    par virement bancaire instantané ou par chèque, au moment de la transaction.
                "
            ],
            [
                'question' => "Combien de temps faut-il pour finaliser le processus de rachat ?",
                'answer' => "
                    En général, le processus peut être finalisé en quelques jours seulement 
                    après l'acceptation de notre offre.
                "
            ],
            [
                'question' => "Y a-t-il des frais cachés ou des coûts supplémentaires associés au processus de rachat ?",
                'answer' => "
                    Non, notre offre est totalement transparente, 
                    et il n'y a aucun frais caché. Nous couvrons tous les coûts liés à la transaction.
                "
            ],
        ];

        $agencies = [
            [
                'name' => 'CarPilot Epagny',
                'address' => '719 Route de Bellegarde, 74330 Epagny Metz-Tessy',
                'hours' => 'Du lundi au vendredi – 9h-12h / 15h-19h. Samedi sur rendez-vous.',
                'phone' => '04.50.23.12.85',
                'email' => 'annecy@carpilot-annecy.fr',
                'map' => 'CarPilot+Epagny',
            ],
            [
                'name' => 'CarPilot Annecy',
                'address' => "1140 Avenue d'Aix les Bains, 74600 Annecy",
                'hours' => 'Du lundi au vendredi – 9h-12h / 15h-19h. Samedi sur rendez-vous.',
                'phone' => '04.50.46.77.94',
                'email' => 'annecy@carpilot.fr',
                'map' => 'CarPilot+Annecy',
            ],
            [
                'name' => 'CarPilot Valence',
                'address' => '52-74 Rue Barthélemy de Laffemas, 26000 Valence',
                'hours' => 'Du lundi au vendredi – 9h-12h / 15h-19h. Samedi sur rendez-vous.',
                'phone' => '04.75.00.00.00',
                'email' => 'contact@carpilot-valence.fr',
                'map' => 'CCI+Valence',
            ],
            [
                'name' => 'CarPilot Unieux',
                'address' => '8 Rue Charles de Gaulle, 42240 Unieux',
                'hours' => 'Du lundi au vendredi – 9h-12h / 15h-19h. Samedi sur rendez-vous.',
                'phone' => '04.77.61.21.40',
                'email' => 'unieux@carpilot.fr',
                'map' => 'CarPilot+Unieux',
            ],
        ];

        $reviews = [
            [
                'img'  => '/images/photo-avis-1.png',
                'name' => 'Charles HENRY',
                'text' => 'Incroyable, ma voiture est estimée et vendue<br>en quelques clics seulement !',
            ],
            [
                'img'  => '/images/photo-avis-2.png',
                'name' => 'Iness DEKLAPE',
                'text' => 'Très satisfaite du service, rapidité et fiabilité<br>au rendez-vous. Je recommande !',
            ],
            [
                'img'  => '/images/photo-avis-1.png',
                'name' => 'Julien MARCHEL',
                'text' => 'Service nickel, estimation rapide et sérieuse, je recommande !',
            ],
            [
                'img'  => '/images/photo-avis-2.png',
                'name' => 'Sarah LEMOINE',
                'text' => 'Rachat immédiat et prix au top ! Rien à dire.',
            ],
        ];

        return $this->render('home/index.html.twig', [
            'faqs' => $faqs,
            'steps' => $steps,
            'agencies' => $agencies,
            'reviews' => $reviews
        ]);
    }
}
