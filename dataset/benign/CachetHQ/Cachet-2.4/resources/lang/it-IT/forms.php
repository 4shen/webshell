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

    // Setup form fields
    'setup' => [
        'email'            => 'Email',
        'username'         => 'Nome Utente',
        'password'         => 'Password',
        'site_name'        => 'Nome del sito',
        'site_domain'      => 'Dominio del sito',
        'site_timezone'    => 'Seleziona il tuo fuso orario',
        'site_locale'      => 'Seleziona la lingua',
        'enable_google2fa' => 'Abilita la Verifica in Due Passaggi di Google',
        'cache_driver'     => 'Driver per la cache',
        'queue_driver'     => 'Queue Driver',
        'session_driver'   => 'Driver per la sessione',
        'mail_driver'      => 'Driver di posta elettronica',
        'mail_host'        => 'Host di posta elettronica',
        'mail_address'     => 'Posta dall\'indirizzo',
        'mail_username'    => 'Nome utente di posta',
        'mail_password'    => 'Password di posta elettronica',
    ],

    // Login form fields
    'login' => [
        'login'         => 'Nome Utente o Email',
        'email'         => 'Email',
        'password'      => 'Password',
        '2fauth'        => 'Codice di Autenticazione',
        'invalid'       => 'Il nome utente o la password non sono validi',
        'invalid-token' => 'Token non valido',
        'cookies'       => 'È necessario abilitare i cookie per effettuare l\'accesso.',
        'rate-limit'    => 'Sei troppo veloce.',
        'remember_me'   => 'Ricordami',
    ],

    // Incidents form fields
    'incidents' => [
        'name'               => 'Nome',
        'status'             => 'Stato',
        'component'          => 'Componente',
        'component_status'   => 'Component Status',
        'message'            => 'Messaggio',
        'message-help'       => 'Si può anche utilizzare il linguaggio di Markdown.',
        'occurred_at'        => 'When did this incident occur?',
        'notify_subscribers' => 'Notificare gli iscritti?',
        'notify_disabled'    => 'Due to scheduled maintenance, notifications about this incident or its components will be suppressed.',
        'visibility'         => 'Visibilità segnalazione',
        'stick_status'       => 'Stick Incident',
        'stickied'           => 'Stickied',
        'not_stickied'       => 'Not Stickied',
        'public'             => 'Visibile al pubblico',
        'logged_in_only'     => 'Visibile solo agli utenti registrati',
        'templates'          => [
            'name'     => 'Nome',
            'template' => 'Modello',
            'twig'     => 'I modelli di segnalazione possono usare il linguaggio di templating <a href="http://twig.sensiolabs.org/" target="_blank">Twig</a>.',
        ],
    ],

    'schedules' => [
        'name'         => 'Nome',
        'status'       => 'Stato',
        'message'      => 'Messaggio',
        'message-help' => 'Si può anche utilizzare il linguaggio di Markdown.',
        'scheduled_at' => 'When is this maintenance scheduled for?',
        'completed_at' => 'When did this maintenance complete?',
        'templates'    => [
            'name'     => 'Nome',
            'template' => 'Modello',
            'twig'     => 'I modelli di segnalazione possono usare il linguaggio di templating <a href="http://twig.sensiolabs.org/" target="_blank">Twig</a>.',
        ],
    ],

    // Components form fields
    'components' => [
        'name'        => 'Nome',
        'status'      => 'Stato',
        'group'       => 'Gruppo',
        'description' => 'Descrizione',
        'link'        => 'Collegamento',
        'tags'        => 'Etichette',
        'tags-help'   => 'Separati da virgole.',
        'enabled'     => 'Componente abilitato?',

        'groups' => [
            'name'                     => 'Nome',
            'collapsing'               => 'Expand/Collapse options',
            'visible'                  => 'Sempre espanso',
            'collapsed'                => 'Comprimere il gruppo come impostazione predefinita',
            'collapsed_incident'       => 'Comprimere il gruppo, ma espandere se ci sono problemi',
            'visibility'               => 'Visibilità',
            'visibility_public'        => 'Visible to public',
            'visibility_authenticated' => 'Visible only to logged in users',
        ],
    ],

    // Action form fields
    'actions' => [
        'name'               => 'Nome',
        'description'        => 'Descrizione',
        'start_at'           => 'Schedule start time',
        'timezone'           => 'Fuso orario',
        'schedule_frequency' => 'Schedule frequency (in seconds)',
        'completion_latency' => 'Completion latency (in seconds)',
        'group'              => 'Gruppo',
        'active'             => 'Attivo?',
        'groups'             => [
            'name' => 'Nome del Gruppo',
        ],
    ],

    // Metric form fields
    'metrics' => [
        'name'                     => 'Nome',
        'suffix'                   => 'Suffisso',
        'description'              => 'Descrizione',
        'description-help'         => 'Si può anche utilizzare il linguaggio di Markdown.',
        'display-chart'            => 'Visualizzare il grafico nella pagina di stato?',
        'default-value'            => 'Valore predefinito',
        'calc_type'                => 'Calcolo del metodo di misura',
        'type_sum'                 => 'Somma',
        'type_avg'                 => 'Media',
        'places'                   => 'Cifre decimali',
        'default_view'             => 'Vista predefinita',
        'threshold'                => 'Quanti minuti di soglia tra metriche punti?',
        'visibility'               => 'Visibilità',
        'visibility_authenticated' => 'Visible to authenticated users',
        'visibility_public'        => 'Visible to everybody',
        'visibility_hidden'        => 'Sempre nascosto',

        'points' => [
            'value' => 'Valore',
        ],
    ],

    // Settings
    'settings' => [
        // Application setup
        'app-setup' => [
            'site-name'                             => 'Nome del sito',
            'site-url'                              => 'URL del Sito',
            'display-graphs'                        => 'Visualizzare i grafici nella pagina di stato?',
            'about-this-page'                       => 'Informazioni sulla pagina',
            'days-of-incidents'                     => 'Quanti giorni di segnalazioni mostrare?',
            'time_before_refresh'                   => 'Status page refresh rate (in seconds)',
            'major_outage_rate'                     => 'Major outage threshold (in %)',
            'banner'                                => 'Immagine del banner',
            'banner-help'                           => "It's recommended that you upload files no bigger than 930px wide",
            'subscribers'                           => 'Permettere alle persone di iscriversi alle notifiche via email?',
            'suppress_notifications_in_maintenance' => 'Suppress notifications when incident occurs during maintenance period?',
            'skip_subscriber_verification'          => 'Skip verifying of users? (Be warned, you could be spammed)',
            'automatic_localization'                => 'Tradurre automaticamente la tua pagina di stato nella lingua del visitatore?',
            'enable_external_dependencies'          => 'Enable Third Party Dependencies (Google Fonts, Trackers, etc...)',
            'show_timezone'                         => 'Show the timezone the status page is running in',
            'only_disrupted_days'                   => 'Only show days containing incidents in the timeline?',
        ],
        'analytics' => [
            'analytics_google'       => 'Codice Google Analytics',
            'analytics_gosquared'    => 'Codice GoSquared Analytics',
            'analytics_piwik_url'    => 'URL dell\'istanza Piwik (senza http(s)://)',
            'analytics_piwik_siteid' => 'Id del sito Piwik',
        ],
        'localization' => [
            'site-timezone'        => 'Fuso orario del sito',
            'site-locale'          => 'Lingua del sito',
            'date-format'          => 'Formato della data',
            'incident-date-format' => 'Formato timestamp della segnalazione',
        ],
        'security' => [
            'allowed-domains'           => 'Domini consentiti',
            'allowed-domains-help'      => 'Separati da virgola. Il dominio impostato sopra è automaticamente consentito per impostazione predefinita.',
            'always-authenticate'       => 'Always authenticate',
            'always-authenticate-help'  => 'Require login to view any Cachet page',
        ],
        'stylesheet' => [
            'custom-css' => 'Foglio di stile personalizzato',
        ],
        'theme' => [
            'background-color'        => 'Colore di sfondo',
            'background-fills'        => 'Riempimento sfondo (componenti, segnalazioni, footer)',
            'banner-background-color' => 'Colore di sfondo del banner',
            'banner-padding'          => 'Padding del banner',
            'fullwidth-banner'        => 'Enable full width banner?',
            'text-color'              => 'Colore del testo',
            'dashboard-login'         => 'Mostrare pulsante del pannello amministrativo nel footer?',
            'reds'                    => 'Rosso (utilizzato per gli errori)',
            'blues'                   => 'Blu (usato per le informazioni)',
            'greens'                  => 'Verde (usato per operazioni andate a buon fine)',
            'yellows'                 => 'Giallo (utilizzato per gli avvisi)',
            'oranges'                 => 'Orange (usato per notifiche)',
            'metrics'                 => 'Riempimento di metriche',
            'links'                   => 'Collegamenti',
        ],
    ],

    'user' => [
        'username'       => 'Nome Utente',
        'email'          => 'Email',
        'password'       => 'Password',
        'api-token'      => 'API Token',
        'api-token-help' => 'Rigenerare il tuo API token impedirà alle applicazioni esistenti di accedere a Cachet.',
        'gravatar'       => 'Cambia la tua immagine del profilo presso Gravatar.',
        'user_level'     => 'Livello utente',
        'levels'         => [
            'admin' => 'Amministratore',
            'user'  => 'Utente',
        ],
        '2fa' => [
            'help' => 'L\'attivazione dell\'autenticazione in due passaggi aumenta la sicurezza del tuo account. Sarà necessario scaricare <a href="https://support.google.com/accounts/answer/1066447?hl=en"> Google Authenticator</a> o un\'app simile sul tuo dispositivo mobile. Quando si effettua l\'accesso dovrai fornire un token generato dall\'applicazione.',
        ],
        'team' => [
            'description' => 'Invita i membri del gruppo inserendo qui i loro indirizzi e-mail.',
            'email'       => 'Your Team Members Email Address',
        ],
    ],

    'general' => [
        'timezone' => 'Select Timezone',
    ],

    // Buttons
    'add'            => 'Aggiungi',
    'save'           => 'Salva',
    'update'         => 'Aggiorna',
    'create'         => 'Crea',
    'edit'           => 'Modifica',
    'delete'         => 'Elimina',
    'submit'         => 'Invia',
    'cancel'         => 'Cancella',
    'remove'         => 'Rimuovi',
    'invite'         => 'Invita',
    'signup'         => 'Registrati',
    'manage_updates' => 'Manage Updates',

    // Other
    'optional' => '* Opzionale',
];
