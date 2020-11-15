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

    'dashboard'          => 'Pannello amministrativo',
    'writeable_settings' => 'The Cachet settings directory is not writeable. Please make sure that <code>./bootstrap/cachet</code> is writeable by the web server.',

    // Incidents
    'incidents' => [
        'title'                    => 'Incidents & Maintenance',
        'incidents'                => 'Incidenti',
        'logged'                   => '{0}There are no incidents, good work.|[1]You have logged one incident.|[2,*]You have reported <strong>:count</strong> incidents.',
        'incident-create-template' => 'Crea Modello',
        'incident-templates'       => 'Modelli di segnalazione',
        'updates'                  => [
            'title'   => 'Incident updates for :incident',
            'count'   => '{0}Zero Updates|[1]One Update|[2]Two Updates|[3,*]Several Updates',
            'add'     => [
                'title'   => 'Create new incident update',
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
            'title'   => 'Riporta un problema',
            'success' => 'Segnalazione aggiunta.',
            'failure' => 'C\'è stato un problema con l\'aggiunta dela segnalazione, si prega di riprovare.',
        ],
        'edit' => [
            'title'   => 'Modifica una segnalazione',
            'success' => 'Segnalazione aggiornata.',
            'failure' => 'C\'è stato un problema con la modifica della segnalazione, si prega di riprovare.',
        ],
        'delete' => [
            'success' => 'La segnalazione è stata eliminata e non verrà visualizzata sulla tua pagina di stato.',
            'failure' => 'Non è stato possibile eliminare la segnalazione, si prega di riprovare.',
        ],

        // Incident templates
        'templates' => [
            'title' => 'Modelli di segnalazione',
            'add'   => [
                'title'   => 'Crea un modello di segnalazione',
                'message' => 'Create your first incident template.',
                'success' => 'Il tuo nuovo modello di segnalazione è stato creato.',
                'failure' => 'Qualcosa è andato storto con il modello di segnalazione.',
            ],
            'edit' => [
                'title'   => 'Modifica Modello',
                'success' => 'Il modello di segnalazione è stato aggiornato.',
                'failure' => 'Qualcosa è andato storto con l\'aggiornamento del modello di segnalazione',
            ],
            'delete' => [
                'success' => 'Il modello di segnalazione è stato eliminato.',
                'failure' => 'Non è stato possibile eliminare il modello di segnalazione, si prega di riprovare.',
            ],
        ],
    ],

    // Incident Maintenance
    'schedule' => [
        'schedule'     => 'Maintenance',
        'logged'       => '{0}There has been no Maintenance, good work.|[1]You have logged one schedule.|[2,*]You have reported <strong>:count</strong> schedules.',
        'scheduled_at' => 'Pianificato alle :timestamp',
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
        'components'         => 'Componenti',
        'component_statuses' => 'Stati del componente',
        'listed_group'       => 'Raggruppati sotto :name',
        'add'                => [
            'title'   => 'Aggiungi un componente',
            'message' => 'È necessario aggiungere un componente.',
            'success' => 'Componente creato.',
            'failure' => 'Qualcosa è andato storto con il componente, si prega di riprovare.',
        ],
        'edit' => [
            'title'   => 'Modifica un componente',
            'success' => 'Componente aggiornato.',
            'failure' => 'Qualcosa è andato storto con il componente, si prega di riprovare.',
        ],
        'delete' => [
            'success' => 'Il componente è stato eliminato!',
            'failure' => 'Non è stato possibile eliminare il componente, si prega di riprovare.',
        ],

        // Component groups
        'groups' => [
            'groups'        => 'Gruppo del componente | Gruppi del componente',
            'no_components' => 'È necessario aggiungere un gruppo di componenti.',
            'add'           => [
                'title'   => 'Aggiungi un gruppo di componenti',
                'success' => 'Gruppo di componenti aggiunto.',
                'failure' => 'Qualcosa è andato storto con il componente, si prega di riprovare.',
            ],
            'edit' => [
                'title'   => 'Modifica un gruppo di componenti',
                'success' => 'Gruppo di componenti aggiornato.',
                'failure' => 'Qualcosa è andato storto con il componente, si prega di riprovare.',
            ],
            'delete' => [
                'success' => 'Il gruppo di componenti è stato eliminato!',
                'failure' => 'Non è stato possibile eliminare il gruppo di componenti, si prega di riprovare.',
            ],
        ],
    ],

    // Metrics
    'metrics' => [
        'metrics' => 'Metriche',
        'add'     => [
            'title'   => 'Crea una metrica',
            'message' => 'Si dovrebbe aggiungere un metodo di misura.',
            'success' => 'Metrica creata.',
            'failure' => 'Qualcosa è andato storto con il metodo di misura, si prega di riprovare.',
        ],
        'edit' => [
            'title'   => 'Modifica un metodo di misura',
            'success' => 'Metodo di misura aggiornato.',
            'failure' => 'Qualcosa è andato storto con il metodo di misura, si prega di riprovare.',
        ],
        'delete' => [
            'success' => 'Il metodo di misura è stato eliminato e non verrà più visualizzato nella tua pagina di stato.',
            'failure' => 'Non è stato possibile eliminare il metodo di misura, si prega di riprovare.',
        ],
    ],
    // Subscribers
    'subscribers' => [
        'subscribers'          => 'Iscritti',
        'description'          => 'Gli iscritti riceveranno aggiornamenti via email quando vengono create le segnalazioni o vengono aggiornati i componenti vengono.',
        'description_disabled' => 'To use this feature, you need allow people to signup for notifications.',
        'verified'             => 'Verificato',
        'not_verified'         => 'Non Verificato',
        'subscriber'           => ': email, iscritta :date',
        'no_subscriptions'     => 'Iscritto a tutti gli aggiornamenti',
        'global'               => 'Globally subscribed',
        'add'                  => [
            'title'   => 'Aggiungi un nuovo iscritto',
            'success' => 'L\'iscritto è stato aggiunto!',
            'failure' => 'Qualcosa è andato storto con l\'aggiunta dell\'iscritto, si prega di riprovare.',
            'help'    => 'Immettere ogni iscritto su una nuova riga.',
        ],
        'edit' => [
            'title'   => 'Aggiorna l\'iscritto',
            'success' => 'L\'iscritto è stato aggiornato!',
            'failure' => 'Qualcosa è andato storto con la modifica dell\'iscritto, si prega di riprovare.',
        ],
    ],

    // Team
    'team' => [
        'team'        => 'Gruppo',
        'member'      => 'Membro',
        'profile'     => 'Profilo',
        'description' => 'I membri del gruppo potranno aggiungere, modificare ed editare componenti e segnalazioni.',
        'add'         => [
            'title'   => 'Aggiungi un nuovo membro del gruppo',
            'success' => 'Membro del gruppo aggiunto.',
            'failure' => 'Non è stato possibile aggiungere il membro del gruppo, si prega di riprovare.',
        ],
        'edit' => [
            'title'   => 'Aggiorna il profilo',
            'success' => 'Profilo aggiornato.',
            'failure' => 'Qualcosa è andato storto con l\'aggiornamento del profilo, si prega di riprovare.',
        ],
        'delete' => [
            'success' => 'Il membro del gruppo è stato eliminato e non avrà più accesso al pannello amministrativo!',
            'failure' => 'Non è stato possibile aggiungere il membro del gruppo, si prega di riprovare.',
        ],
        'invite' => [
            'title'   => 'Invita un nuovo membro del gruppo',
            'success' => 'Un invito è stato inviato',
            'failure' => 'Non è stato possibile inviare l\'invito, si prega di riprovare.',
        ],
    ],

    // Settings
    'settings' => [
        'settings'  => 'Impostazioni',
        'app-setup' => [
            'app-setup'   => 'Installazione dell\'applicazione',
            'images-only' => 'Possono essere caricate solo le immagini.',
            'too-big'     => 'Il file che hai caricato è troppo grande. Caricare un\'immagine più piccola di :size',
        ],
        'analytics' => [
            'analytics' => 'Statistiche',
        ],
        'log' => [
            'log' => 'Log',
        ],
        'localization' => [
            'localization' => 'Localizzazione',
        ],
        'customization' => [
            'customization' => 'Personalizzazione',
            'header'        => 'Header HTML personalizzato',
            'footer'        => 'Footer HTML personalizzato',
        ],
        'mail' => [
            'mail'  => 'Mail',
            'test'  => 'Test',
            'email' => [
                'subject' => 'Test notification from Cachet',
                'body'    => 'This is a test notification from Cachet.',
            ],
        ],
        'security' => [
            'security'   => 'Sicurezza',
            'two-factor' => 'Utenti senza autenticazione in due passaggi',
        ],
        'stylesheet' => [
            'stylesheet' => 'Foglio di stile',
        ],
        'theme' => [
            'theme' => 'Tema',
        ],
        'edit' => [
            'success' => 'Impostazioni salvate.',
            'failure' => 'Le impostazioni non possono essere salvate.',
        ],
        'credits' => [
            'credits'       => 'Riconoscimenti',
            'contributors'  => 'Collaboratori',
            'license'       => 'Cachet è un progetto open source con licenza BSD-3, rilasciato da <a href="https://alt-three.com/?utm_source=cachet&utm_medium=credits&utm_campaign=Cachet%20Credit%20Dashboard" target="_blank"> Alt tre Services Limited</a>.',
            'backers-title' => 'Sostenitori e sponsor',
            'backers'       => 'Se si desidera sostenere lo sviluppo futuro, scopri la campagna <a href="https://patreon.com/jbrooksuk" target="_blank">Cachet Patreon</a>.',
            'thank-you'     => 'Grazie a tutti i :count contributori.',
        ],
    ],

    // Login
    'login' => [
        'login'      => 'Accedi',
        'logged_in'  => 'Sei connesso.',
        'welcome'    => 'Bentornato!',
        'two-factor' => 'Inserisci il tuo token.',
    ],

    // Sidebar footer
    'help'        => 'Aiuto',
    'status_page' => 'Pagina di Stato',
    'logout'      => 'Disconnetti',

    // Notifications
    'notifications' => [
        'notifications' => 'Notifiche',
        'awesome'       => 'Meraviglioso.',
        'whoops'        => 'Ops.',
    ],

    // Widgets
    'widgets' => [
        'support'          => 'Supporta Cachet',
        'support_subtitle' => 'Visita la nostra pagina <strong><a href="https://patreon.com/jbrooksuk" target="_blank"> Patreon</a></strong>!',
        'news'             => 'Utime notizie',
        'news_subtitle'    => 'Ottieni l\'aggiornamento più recente',
    ],

    // Welcome modal
    'welcome' => [
        'welcome' => 'Benvenuto nella tua nuova pagina di stato!',
        'message' => 'La pagina di stato è quasi pronta! È possibile configurare queste impostazioni supplementari',
        'close'   => 'Portami direttamente al mio pannello amministrativo',
        'steps'   => [
            'component'  => 'Crea componenti',
            'incident'   => 'Crea segnalazioni',
            'customize'  => 'Personalizza',
            'team'       => 'Aggiungi utenti',
            'api'        => 'Genera un API token',
            'two-factor' => 'Autenticazione con verifica in due passaggi',
        ],
    ],

];
