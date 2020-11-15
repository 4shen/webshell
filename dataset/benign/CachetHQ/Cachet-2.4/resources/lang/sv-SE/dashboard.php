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

    'dashboard'          => 'Översiktspanel',
    'writeable_settings' => 'Cachets inställningskatalog är inte skrivbar. Kontrollera att <code>./bootstrap/cachet</code> är skrivbar av webbservern.',

    // Incidents
    'incidents' => [
        'title'                    => 'Incidents & Maintenance',
        'incidents'                => 'Händelser',
        'logged'                   => '{0}There are no incidents, good work.|[1]You have logged one incident.|[2,*]You have reported <strong>:count</strong> incidents.',
        'incident-create-template' => 'Skapa mall',
        'incident-templates'       => 'Händelsemallar',
        'updates'                  => [
            'title'   => 'Uppdateringar för :incident',
            'count'   => '{0}Inga uppdateringar|[1]En uppdatering|[2]Två uppdateringar|[3,*]Flera uppdateringar',
            'add'     => [
                'title'   => 'Skapa en ny incidentuppdatering',
                'success' => 'Din nya incidentuppdatering har skapats.',
                'failure' => 'Något gick fel under uppdatering av incidenten.',
            ],
            'edit' => [
                'title'   => 'Redigera incidentuppdatering',
                'success' => 'Incidentuppdateringen har uppdaterats.',
                'failure' => 'Something went wrong updating the incident update',
            ],
        ],
        'reported_by'              => 'Rapporterad av :user',
        'add'                      => [
            'title'   => 'Lägg till händelse',
            'success' => 'Incident added.',
            'failure' => 'There was an error adding the incident, please try again.',
        ],
        'edit' => [
            'title'   => 'Redigera en händelse',
            'success' => 'Händelse uppdaterad.',
            'failure' => 'There was an error editing the incident, please try again.',
        ],
        'delete' => [
            'success' => 'Händelsen har tagits bort och kommer inte visas på din statussida.',
            'failure' => 'The incident could not be deleted, please try again.',
        ],

        // Incident templates
        'templates' => [
            'title' => 'Händelsemallar',
            'add'   => [
                'title'   => 'Skapa en händelsemall',
                'message' => 'Create your first incident template.',
                'success' => 'Your new incident template has been created.',
                'failure' => 'Something went wrong with the incident template.',
            ],
            'edit' => [
                'title'   => 'Redigera mall',
                'success' => 'The incident template has been updated.',
                'failure' => 'Something went wrong updating the incident template',
            ],
            'delete' => [
                'success' => 'Händelsen har tagits bort.',
                'failure' => 'The incident template could not be deleted, please try again.',
            ],
        ],
    ],

    // Incident Maintenance
    'schedule' => [
        'schedule'     => 'Planerat underhåll',
        'logged'       => '{0}There has been no Maintenance, good work.|[1]You have logged one schedule.|[2,*]You have reported <strong>:count</strong> schedules.',
        'scheduled_at' => 'Schemalagd till: tidsstämpel',
        'add'          => [
            'title'   => 'Lägg till planerat underhåll',
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
        'components'         => 'Komponenter',
        'component_statuses' => 'Komponentstatus',
        'listed_group'       => 'Grupperade under: namn',
        'add'                => [
            'title'   => 'Lägg till en komponent',
            'message' => 'Du borde lägga till en komponent.',
            'success' => 'Component created.',
            'failure' => 'Something went wrong with the component group, please try again.',
        ],
        'edit' => [
            'title'   => 'Redigera komponent',
            'success' => 'Component updated.',
            'failure' => 'Something went wrong with the component group, please try again.',
        ],
        'delete' => [
            'success' => 'Komponenten har tagits bort!',
            'failure' => 'The component could not be deleted, please try again.',
        ],

        // Component groups
        'groups' => [
            'groups'        => 'Komponentgrupp|Komponentgrupper',
            'no_components' => 'Du borde lägga till en komponentgrupp.',
            'add'           => [
                'title'   => 'Lägg till en komponentgrupp',
                'success' => 'Component group added.',
                'failure' => 'Something went wrong with the component group, please try again.',
            ],
            'edit' => [
                'title'   => 'Redigera komponentgrupp',
                'success' => 'Component group updated.',
                'failure' => 'Something went wrong with the component group, please try again.',
            ],
            'delete' => [
                'success' => 'Komponentgruppen har tagits bort!',
                'failure' => 'The component group could not be deleted, please try again.',
            ],
        ],
    ],

    // Metrics
    'metrics' => [
        'metrics' => 'Mätvärden',
        'add'     => [
            'title'   => 'Skapa ett mätetal',
            'message' => 'Du borde lägga till ett mätetal.',
            'success' => 'Metric created.',
            'failure' => 'Something went wrong with the metric, please try again.',
        ],
        'edit' => [
            'title'   => 'Redigera ett mätetal',
            'success' => 'Metric updated.',
            'failure' => 'Something went wrong with the metric, please try again.',
        ],
        'delete' => [
            'success' => 'Mätetalet har tagits bort och kommer inte längre visas på din statussida.',
            'failure' => 'The metric could not be deleted, please try again.',
        ],
    ],
    // Subscribers
    'subscribers' => [
        'subscribers'          => 'Prenumeranter',
        'description'          => 'Subscribers will receive email updates when incidents are created or components are updated.',
        'description_disabled' => 'To use this feature, you need allow people to signup for notifications.',
        'verified'             => 'Bekräftad',
        'not_verified'         => 'Inte bekräftad',
        'subscriber'           => ':email, subscribed :date',
        'no_subscriptions'     => 'Subscribed to all updates',
        'global'               => 'Globally subscribed',
        'add'                  => [
            'title'   => 'Lägg till en prenumerant',
            'success' => 'Prenumerant tillagd!',
            'failure' => 'Something went wrong adding the subscriber, please try again.',
            'help'    => 'Enter each subscriber on a new line.',
        ],
        'edit' => [
            'title'   => 'Uppdatera prenumerant',
            'success' => 'Prenumerant uppdaterad!',
            'failure' => 'Something went wrong editing the subscriber, please try again.',
        ],
    ],

    // Team
    'team' => [
        'team'        => 'Team',
        'member'      => 'Medlem',
        'profile'     => 'Profil',
        'description' => 'Teammedlemmar kommer kunna lägga till, ändra &amp; redigera komponenter och händelser.',
        'add'         => [
            'title'   => 'Lägg till en ny teammedlem',
            'success' => 'Team member added.',
            'failure' => 'The team member could not be added, please try again.',
        ],
        'edit' => [
            'title'   => 'Uppdatera profil',
            'success' => 'Profile updated.',
            'failure' => 'Something went wrong updating the profile, please try again.',
        ],
        'delete' => [
            'success' => 'Teammedlemen har tagits bort och kommer inte längre ha tillgång till översiktspanelen!',
            'failure' => 'The team member could not be added, please try again.',
        ],
        'invite' => [
            'title'   => 'Bjud in en ny teammedlem',
            'success' => 'Inbjudan har skickats',
            'failure' => 'The invite could not be sent, please try again.',
        ],
    ],

    // Settings
    'settings' => [
        'settings'  => 'Inställningar',
        'app-setup' => [
            'app-setup'   => 'Applikationsinstallation',
            'images-only' => 'Endast bilder kan laddas upp.',
            'too-big'     => 'Filen du försöker ladda upp är för stor. Ladda upp en bild som är mindre än :size',
        ],
        'analytics' => [
            'analytics' => 'Analys',
        ],
        'log' => [
            'log' => 'Log',
        ],
        'localization' => [
            'localization' => 'Platsanpassning',
        ],
        'customization' => [
            'customization' => 'Customization',
            'header'        => 'Custom Header HTML',
            'footer'        => 'Custom Footer HTML',
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
            'security'   => 'Säkerhet',
            'two-factor' => 'Användare utan tvåfaktorsautentisering',
        ],
        'stylesheet' => [
            'stylesheet' => 'Stilmall',
        ],
        'theme' => [
            'theme' => 'Tema',
        ],
        'edit' => [
            'success' => 'Inställningar sparade.',
            'failure' => 'Inställningarna kunde inte sparas.',
        ],
        'credits' => [
            'credits'       => 'Credits',
            'contributors'  => 'Contributors',
            'license'       => 'Cachet is a BSD-3-licensed open source project, released by <a href="https://alt-three.com/?utm_source=cachet&utm_medium=credits&utm_campaign=Cachet%20Credit%20Dashboard" target="_blank">Alt Three Services Limited</a>.',
            'backers-title' => 'Backers & Sponsors',
            'backers'       => 'If you\'d like to support future development, check out the <a href="https://patreon.com/jbrooksuk" target="_blank">Cachet Patreon</a> campaign.',
            'thank-you'     => 'Thank you to each and every one of the :count contributors.',
        ],
    ],

    // Login
    'login' => [
        'login'      => 'Logga in',
        'logged_in'  => 'Du är inloggad.',
        'welcome'    => 'Välkommen tillbaka!',
        'two-factor' => 'Vänligen ange din kod.',
    ],

    // Sidebar footer
    'help'        => 'Hjälp',
    'status_page' => 'Statussida',
    'logout'      => 'Logga ut',

    // Notifications
    'notifications' => [
        'notifications' => 'Notifieringar',
        'awesome'       => 'Enastående.',
        'whoops'        => 'Hoppsan.',
    ],

    // Widgets
    'widgets' => [
        'support'          => 'Support Cachet',
        'support_subtitle' => 'Check out our <strong><a href="https://patreon.com/jbrooksuk" target="_blank">Patreon</a></strong> page!',
        'news'             => 'Latest News',
        'news_subtitle'    => 'Get the latest update',
    ],

    // Welcome modal
    'welcome' => [
        'welcome' => 'Välkommen till din statussida!',
        'message' => 'Din statussida är nästan redo. Du kan vilja konfigerara de här extra inställningarna',
        'close'   => 'I\'m good thanks!',
        'steps'   => [
            'component'  => 'Skapa komponenter',
            'incident'   => 'Skapa händelser',
            'customize'  => 'Anpassa',
            'team'       => 'Lägg till användare',
            'api'        => 'Skapa API-nyckel',
            'two-factor' => 'Tvåfaktorsautensiering',
        ],
    ],

];
