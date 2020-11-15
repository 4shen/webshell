<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    'dashboard'          => 'Tableau de bord',
    'writeable_settings' => 'Le dossier des paramètres de Cachet n\'est pas accessible en écriture. Veuillez vous assurer que le serveur web peut écrire dans <code>./bootstrap/cachet</code>.',

    // Incidents
    'incidents' => [
        'title'                    => 'Incidents & Maintenance',
        'incidents'                => 'Incidents',
        'logged'                   => '{0}There are no incidents, good work.|[1]You have logged one incident.|[2,*]You have reported <strong>:count</strong> incidents.',
        'incident-create-template' => 'Créer un modèle',
        'incident-templates'       => 'Modèles d\'incident',
        'updates'                  => [
            'title'   => 'Incident updates for :incident',
            'count'   => '{0}Zero Updates|[1]One Update|[2]Two Updates|[3,*]Several Updates',
            'add'     => [
                'title'   => 'Créer une mise à jour d\'incident',
                'success' => 'Your new incident update has been created.',
                'failure' => 'Something went wrong with the incident update.',
            ],
            'edit' => [
                'title'   => 'Edit incident update',
                'success' => 'The incident update has been updated.',
                'failure' => 'Something went wrong updating the incident update',
            ],
        ],
        'reported_by'              => 'Reported by :user',
        'add'                      => [
            'title'   => 'Ajouter un incident',
            'success' => 'Incident ajouté.',
            'failure' => 'Une erreur s\'est produite en ajoutant l\'incident, veuillez réessayer.',
        ],
        'edit' => [
            'title'   => 'Modifier un incident',
            'success' => 'Incident mis à jour.',
            'failure' => 'Une erreur s\'est produite en modifiant l\'incident, veuillez réessayer.',
        ],
        'delete' => [
            'success' => 'L\'incident a été supprimé et ne sera pas affiché sur votre page de statut.',
            'failure' => 'L\'incident n\'a pas pu être supprimé. Veuillez réessayer.',
        ],

        // Incident templates
        'templates' => [
            'title' => 'Modèles d\'incident',
            'add'   => [
                'title'   => 'Créer un modèle d\'incident',
                'message' => 'Create your first incident template.',
                'success' => 'Votre nouveau modèle d\'incident a été créé.',
                'failure' => 'Une erreur est survenue avec le modèle d\'incident.',
            ],
            'edit' => [
                'title'   => 'Éditer le modèle',
                'success' => 'Le modèle d\'incident a été mis à jour.',
                'failure' => 'Une erreur est survenue lors de la mise à jour du modèle d\'incident',
            ],
            'delete' => [
                'success' => 'Le modèle d\'incident a été supprimé.',
                'failure' => 'Le modèle d\'incident n\'a pas pu être supprimé. Veuillez réessayer.',
            ],
        ],
    ],

    // Incident Maintenance
    'schedule' => [
        'schedule'     => 'Maintenance',
        'logged'       => '{0}There has been no Maintenance, good work.|[1]You have logged one schedule.|[2,*]You have reported <strong>:count</strong> schedules.',
        'scheduled_at' => 'Planifiée à :timestamp',
        'add'          => [
            'title'   => 'Add Maintenance',
            'success' => 'Maintenance added.',
            'failure' => 'Something went wrong adding the Maintenance, please try again.',
        ],
        'edit' => [
            'title'   => 'Edit Maintenance',
            'success' => 'Maintenance has been updated!',
            'failure' => 'Something went wrong editing the Maintenance, please try again.',
        ],
        'delete' => [
            'success' => 'The Maintenance has been deleted and will not show on your status page.',
            'failure' => 'The Maintenance could not be deleted, please try again.',
        ],
    ],

    // Components
    'components' => [
        'components'         => 'Composants',
        'component_statuses' => 'Statut des composants',
        'listed_group'       => 'Groupés par :nom',
        'add'                => [
            'title'   => 'Ajouter un composant',
            'message' => 'Vous devez ajouter un composant.',
            'success' => 'Composant créé.',
            'failure' => 'Un problème est survenu avec le composant. Veuillez réessayer.',
        ],
        'edit' => [
            'title'   => 'Modifier un composant',
            'success' => 'Composant mis à jour.',
            'failure' => 'Un problème est survenu avec le composant. Veuillez réessayer.',
        ],
        'delete' => [
            'success' => 'Le composant a été supprimé !',
            'failure' => 'Le composant n\'a pas pu être supprimé. Veuillez réessayer.',
        ],

        // Component groups
        'groups' => [
            'groups'        => 'Groupe de composants|Groupes de composants',
            'no_components' => 'Vous devez ajouter un groupe de composants.',
            'add'           => [
                'title'   => 'Ajouter un groupe de composants',
                'success' => 'Groupe de composants ajouté.',
                'failure' => 'Un problème est survenu avec le composant. Veuillez réessayer.',
            ],
            'edit' => [
                'title'   => 'Modifier un groupe de composants',
                'success' => 'Groupe de composants mis à jour.',
                'failure' => 'Un problème est survenu avec le composant. Veuillez réessayer.',
            ],
            'delete' => [
                'success' => 'Le groupe de composants a été supprimé !',
                'failure' => 'Le groupe de composants n\'a pas pu être supprimé. Veuillez réessayer.',
            ],
        ],
    ],

    // Metrics
    'metrics' => [
        'metrics' => 'Indicateur',
        'add'     => [
            'title'   => 'Créer une métrique',
            'message' => 'Vous devez ajouter un indicateur.',
            'success' => 'Indicateur créé.',
            'failure' => 'Une erreur est survenue avec l\'indicateur. Veuillez réessayer.',
        ],
        'edit' => [
            'title'   => 'Modifier une métrique',
            'success' => 'Indicateur mise à jour.',
            'failure' => 'Une erreur est survenue avec l\'indicateur. Veuillez réessayer.',
        ],
        'delete' => [
            'success' => 'La métrique a été supprimé et ne sera plus visible sur votre page de statut.',
            'failure' => 'L\'indicateur n\'a pas pu être supprimé. Veuillez réessayer.',
        ],
    ],
    // Subscribers
    'subscribers' => [
        'subscribers'          => 'Abonnés',
        'description'          => 'Les abonnés recevront des notifications par e-mail lorsque des incidents sont créés ou des composants sont mis à jour.',
        'description_disabled' => 'To use this feature, you need allow people to signup for notifications.',
        'verified'             => 'Vérifié',
        'not_verified'         => 'Non vérifié',
        'subscriber'           => ':email, abonné à :date',
        'no_subscriptions'     => 'Souscrire à toutes les mises à jour',
        'global'               => 'Globally subscribed',
        'add'                  => [
            'title'   => 'Ajouter un abonné',
            'success' => 'L\'abonné a été ajouté !',
            'failure' => 'Une erreur s\'est produite lors de l\'ajout de l\'abonné. Veuillez réessayer.',
            'help'    => 'Saisissez un abonné par ligne.',
        ],
        'edit' => [
            'title'   => 'Mettre à jour l\'abonné',
            'success' => 'L\'abonné a été mis à jour !',
            'failure' => 'Une erreur s\'est produite lors de la modification de l\'abonné. Veuillez réessayer.',
        ],
    ],

    // Team
    'team' => [
        'team'        => 'Équipe',
        'member'      => 'Membre',
        'profile'     => 'Profil utilisateur',
        'description' => 'Les membres de l\'équipe pourront ajouter &amp; modifier les composants et les incidents.',
        'add'         => [
            'title'   => 'Ajouter un nouveau membre de l\'équipe',
            'success' => 'Membre de l\'équipe a ajouté.',
            'failure' => 'Le membre de l\'équipe n\'a pas pu être ajouté. Veuillez réessayer.',
        ],
        'edit' => [
            'title'   => 'Mettre le profil à jour',
            'success' => 'Profil mis à jour.',
            'failure' => 'Une erreur s\'est produite lors de la mise à jour du profil. Veuillez réessayer.',
        ],
        'delete' => [
            'success' => 'Le membre de l\'équipe a été supprimé et n\'aura plus l\'accès au tableau de bord !',
            'failure' => 'Le membre de l\'équipe n\'a pas pu être ajouté. Veuillez réessayer.',
        ],
        'invite' => [
            'title'   => 'Inviter un nouveau membre de l\'équipe',
            'success' => 'Nouveau membre invité.',
            'failure' => 'L\'invitation n\'a pas pu être envoyé. Veuillez réessayer.',
        ],
    ],

    // Settings
    'settings' => [
        'settings'  => 'Paramètres',
        'app-setup' => [
            'app-setup'   => 'Configuration de l\'application',
            'images-only' => 'Seules les images peuvent être envoyées.',
            'too-big'     => 'Le fichier envoyé est trop grand. Envoyer une image d\'une taille inférieur à :size',
        ],
        'analytics' => [
            'analytics' => 'Analytics',
        ],
        'log' => [
            'log' => 'Log',
        ],
        'localization' => [
            'localization' => 'Localisation',
        ],
        'customization' => [
            'customization' => 'Personnalisation',
            'header'        => 'Entête HTML personnalisé',
            'footer'        => 'Pied de page HTML personnalisé',
        ],
        'mail' => [
            'mail'  => 'Courriel',
            'test'  => 'Test',
            'email' => [
                'subject' => 'Tester la notification depuis Cachet',
                'body'    => 'Ceci est une notification de test depuis Cachet.',
            ],
        ],
        'security' => [
            'security'   => 'Sécurité',
            'two-factor' => 'Utilisateurs sans authentification à deux facteurs',
        ],
        'stylesheet' => [
            'stylesheet' => 'Feuille de style',
        ],
        'theme' => [
            'theme' => 'Thème',
        ],
        'edit' => [
            'success' => 'Paramètres sauvegardés.',
            'failure' => 'Les paramètres n\'ont pas pu être sauvegardés.',
        ],
        'credits' => [
            'credits'       => 'Crédits',
            'contributors'  => 'Contributeurs',
            'license'       => 'Cachet est un logiciel libre sous licence BSD-3 édité par <a href="https://alt-three.com/?utm_source=cachet&utm_medium=credits&utm_campaign=Cachet%20Credit%20Dashboard" target="_blank">Alt Three Services Limited</a>.',
            'backers-title' => 'Partenaires financiers et sponsors',
            'backers'       => 'Si vous souhaitez aider des développements futurs jetez un œil à la campagne <a href="https://patreon.com/jbrooksuk" target="_blank">Cachet Patreon</a>.',
            'thank-you'     => 'Merci à chacun des :count contributeurs.',
        ],
    ],

    // Login
    'login' => [
        'login'      => 'Connexion',
        'logged_in'  => 'Vous êtes connecté.',
        'welcome'    => 'Re-bonjour !',
        'two-factor' => 'Veuillez entrer votre jeton.',
    ],

    // Sidebar footer
    'help'        => 'Aide',
    'status_page' => 'Page de statut',
    'logout'      => 'Déconnexion',

    // Notifications
    'notifications' => [
        'notifications' => 'Notifications',
        'awesome'       => 'Fantastique.',
        'whoops'        => 'Oups.',
    ],

    // Widgets
    'widgets' => [
        'support'          => 'Supportez Cachet',
        'support_subtitle' => 'Jetez un œil à la page <strong><a href="https://patreon.com/jbrooksuk" target="_blank">Patreon</a></strong>!',
        'news'             => 'Dernières actualités',
        'news_subtitle'    => 'Obtenez les dernières mises à jour',
    ],

    // Welcome modal
    'welcome' => [
        'welcome' => 'Bienvenue sur votre page de statut !',
        'message' => 'Votre page de statut est presque prête ! Vous voudrez peut-être configurer ces paramètres supplémentaires',
        'close'   => 'Aller directement à mon tableau de bord',
        'steps'   => [
            'component'  => 'Créer des composants',
            'incident'   => 'Créer des incidents',
            'customize'  => 'Personnaliser',
            'team'       => 'Ajouter des utilisateurs',
            'api'        => 'Générer un jeton API',
            'two-factor' => 'Authentification à deux facteurs',
        ],
    ],

];
