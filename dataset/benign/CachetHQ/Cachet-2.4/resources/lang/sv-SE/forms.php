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
        'email'            => 'E-post',
        'username'         => 'Användarnamn',
        'password'         => 'Lösenord',
        'site_name'        => 'Webbplatsens namn',
        'site_domain'      => 'Webbplatsens domän',
        'site_timezone'    => 'Välj din tidzon',
        'site_locale'      => 'Välj ditt språk',
        'enable_google2fa' => 'Aktivera Google tvåfaktorsautentisering',
        'cache_driver'     => 'Cachedrivrutin',
        'queue_driver'     => 'Queue Driver',
        'session_driver'   => 'Sessionsdrivrutin',
        'mail_driver'      => 'Mail Driver',
        'mail_host'        => 'Mail Host',
        'mail_address'     => 'Mail From Address',
        'mail_username'    => 'Mail Username',
        'mail_password'    => 'Mail Password',
    ],

    // Login form fields
    'login' => [
        'login'         => 'Användarnamn eller e-postadress',
        'email'         => 'E-post',
        'password'      => 'Lösenord',
        '2fauth'        => 'Autentiseringskod',
        'invalid'       => 'Ogiltigt användarnamn eller lösenord',
        'invalid-token' => 'Ogiltig nyckel',
        'cookies'       => 'Du måste aktivera cookies för att kunna logga in.',
        'rate-limit'    => 'Rate limit exceeded.',
        'remember_me'   => 'Remember me',
    ],

    // Incidents form fields
    'incidents' => [
        'name'               => 'Namn',
        'status'             => 'Status',
        'component'          => 'Komponent',
        'component_status'   => 'Component Status',
        'message'            => 'Meddelande',
        'message-help'       => 'Du kan även använda Markdown.',
        'occurred_at'        => 'When did this incident occur?',
        'notify_subscribers' => 'Meddela prenumeranter?',
        'notify_disabled'    => 'Due to scheduled maintenance, notifications about this incident or its components will be suppressed.',
        'visibility'         => 'Incident Visibility',
        'stick_status'       => 'Stick Incident',
        'stickied'           => 'Stickied',
        'not_stickied'       => 'Not Stickied',
        'public'             => 'Kan ses av allmänheten',
        'logged_in_only'     => 'Endast synlig för inloggade användare',
        'templates'          => [
            'name'     => 'Namn',
            'template' => 'Mall',
            'twig'     => 'Händelsmallar kan använda <a href="http://twig.sensiolabs.org/" target="_blank">Twig</a>-mallspråk.',
        ],
    ],

    'schedules' => [
        'name'         => 'Namn',
        'status'       => 'Status',
        'message'      => 'Meddelande',
        'message-help' => 'Du kan även använda Markdown.',
        'scheduled_at' => 'When is this maintenance scheduled for?',
        'completed_at' => 'When did this maintenance complete?',
        'templates'    => [
            'name'     => 'Namn',
            'template' => 'Mall',
            'twig'     => 'Händelsmallar kan använda <a href="http://twig.sensiolabs.org/" target="_blank">Twig</a>-mallspråk.',
        ],
    ],

    // Components form fields
    'components' => [
        'name'        => 'Namn',
        'status'      => 'Status',
        'group'       => 'Grupp',
        'description' => 'Beskrivning',
        'link'        => 'Länk',
        'tags'        => 'Etiketter',
        'tags-help'   => 'Kommaseparerade.',
        'enabled'     => 'Komponent aktiverad?',

        'groups' => [
            'name'                     => 'Namn',
            'collapsing'               => 'Expand/Collapse options',
            'visible'                  => 'Always expanded',
            'collapsed'                => 'Collapse the group by default',
            'collapsed_incident'       => 'Collapse the group, but expand if there are issues',
            'visibility'               => 'Visibility',
            'visibility_public'        => 'Visible to public',
            'visibility_authenticated' => 'Visible only to logged in users',
        ],
    ],

    // Action form fields
    'actions' => [
        'name'               => 'Namn',
        'description'        => 'Beskrivning',
        'start_at'           => 'Schedule start time',
        'timezone'           => 'Timezone',
        'schedule_frequency' => 'Schedule frequency (in seconds)',
        'completion_latency' => 'Completion latency (in seconds)',
        'group'              => 'Grupp',
        'active'             => 'Active?',
        'groups'             => [
            'name' => 'Group Name',
        ],
    ],

    // Metric form fields
    'metrics' => [
        'name'                     => 'Namn',
        'suffix'                   => 'Suffix',
        'description'              => 'Beskrivning',
        'description-help'         => 'Du kan även använda Markdown.',
        'display-chart'            => 'Visa diagram på statussidan?',
        'default-value'            => 'Standardvärde',
        'calc_type'                => 'Beräkning av mätetal',
        'type_sum'                 => 'Summa',
        'type_avg'                 => 'Medelvärde',
        'places'                   => 'Decimalplatser',
        'default_view'             => 'Standardvy',
        'threshold'                => 'How many minutes of threshold between metric points?',
        'visibility'               => 'Visibility',
        'visibility_authenticated' => 'Visible to authenticated users',
        'visibility_public'        => 'Visible to everybody',
        'visibility_hidden'        => 'Always hidden',

        'points' => [
            'value' => 'Värde',
        ],
    ],

    // Settings
    'settings' => [
        // Application setup
        'app-setup' => [
            'site-name'                             => 'Webbplatsens namn',
            'site-url'                              => 'Webbplatsens URL',
            'display-graphs'                        => 'Visa grafer på statussidan?',
            'about-this-page'                       => 'Om den här sidan',
            'days-of-incidents'                     => 'Hur många dagar av händelser ska visas?',
            'time_before_refresh'                   => 'Status page refresh rate (in seconds)',
            'major_outage_rate'                     => 'Major outage threshold (in %)',
            'banner'                                => 'Banner Image',
            'banner-help'                           => "It's recommended that you upload files no bigger than 930px wide",
            'subscribers'                           => 'Tillåt att registrera sig för notifikationer via e-post?',
            'suppress_notifications_in_maintenance' => 'Suppress notifications when incident occurs during maintenance period?',
            'skip_subscriber_verification'          => 'Skip verifying of users? (Be warned, you could be spammed)',
            'automatic_localization'                => 'Automatically localise your status page to your visitor\'s language?',
            'enable_external_dependencies'          => 'Enable Third Party Dependencies (Google Fonts, Trackers, etc...)',
            'show_timezone'                         => 'Show the timezone the status page is running in',
            'only_disrupted_days'                   => 'Only show days containing incidents in the timeline?',
        ],
        'analytics' => [
            'analytics_google'       => 'Google Analytics-kod',
            'analytics_gosquared'    => 'GoSquared Analytics-code',
            'analytics_piwik_url'    => 'URL till din Piwik-instans (utan http(s)://)',
            'analytics_piwik_siteid' => 'Piwik\'s sajt-id',
        ],
        'localization' => [
            'site-timezone'        => 'Webbplatsens tidszon',
            'site-locale'          => 'Webbplatsspråk',
            'date-format'          => 'Datumformat',
            'incident-date-format' => 'Händelsens tidsstämpelformat',
        ],
        'security' => [
            'allowed-domains'           => 'Tillåtna domäner',
            'allowed-domains-help'      => 'Kommaseparerad. Domänerna ovan tillåts automatiskt som standard.',
            'always-authenticate'       => 'Always authenticate',
            'always-authenticate-help'  => 'Require login to view any Cachet page',
        ],
        'stylesheet' => [
            'custom-css' => 'Custom Stylesheet',
        ],
        'theme' => [
            'background-color'        => 'Background color',
            'background-fills'        => 'Bakgrundsfärg (komponenter, händelser, sidfot)',
            'banner-background-color' => 'Bakgrundsfärg för banner',
            'banner-padding'          => 'Bannerutfyllnad',
            'fullwidth-banner'        => 'Enable full width banner?',
            'text-color'              => 'Text color',
            'dashboard-login'         => 'Visa länk till översiktspanelen i sidfoten?',
            'reds'                    => 'Röd (används för fel)',
            'blues'                   => 'Blå (används för information)',
            'greens'                  => 'Grön (används för lyckanden)',
            'yellows'                 => 'Gul (används för varningar)',
            'oranges'                 => 'Orange (används för notiser)',
            'metrics'                 => 'Mätetälsfyllnad',
            'links'                   => 'Länkar',
        ],
    ],

    'user' => [
        'username'       => 'Användarnamn',
        'email'          => 'E-post',
        'password'       => 'Lösenord',
        'api-token'      => 'API-nyckel',
        'api-token-help' => 'Att återskapa din API-nyckel kommer hindra existerande applikationer från att komma åt Cachet.',
        'gravatar'       => 'Ändra din profilbild hos Gravatar.',
        'user_level'     => 'Användarnivå',
        'levels'         => [
            'admin' => 'Admin',
            'user'  => 'Användare',
        ],
        '2fa' => [
            'help' => 'Tvåfaktorsautentisering ökar säkerheten på ditt konto. Du behöver ladda ner <a href="https://support.google.com/accounts/answer/1066447?hl=en">Google Authenticator</a> eller någon liknande app på din mobila enhet. När du loggar in kommer du få ange en kod som genereras av appen.',
        ],
        'team' => [
            'description' => 'Bjud in dina teammedlemmar genom att fylla i deras epostadresser här.',
            'email'       => 'Your Team Members Email Address',
        ],
    ],

    'general' => [
        'timezone' => 'Select Timezone',
    ],

    // Buttons
    'add'            => 'Lägg till',
    'save'           => 'Spara',
    'update'         => 'Uppdatera',
    'create'         => 'Skapa',
    'edit'           => 'Redigera',
    'delete'         => 'Radera',
    'submit'         => 'Skicka',
    'cancel'         => 'Avbryt',
    'remove'         => 'Ta bort',
    'invite'         => 'Bjud In',
    'signup'         => 'Registrera dig',
    'manage_updates' => 'Manage Updates',

    // Other
    'optional' => 'Valfri',
];
