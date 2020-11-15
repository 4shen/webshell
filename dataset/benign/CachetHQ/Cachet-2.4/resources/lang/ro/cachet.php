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
    // Components
    'components' => [
        'last_updated' => 'Last updated :timestamp',
        'status'       => [
            1 => 'Operaţional',
            2 => 'Probleme de performanţă',
            3 => 'Ȋntrerupere parțială',
            4 => 'Ȋntrerupere gravă',
        ],
        'group' => [
            'other' => 'Other Components',
        ],
    ],

    // Incidents
    'incidents' => [
        'none'          => 'No incidents reported',
        'past'          => 'Incidente anterioare',
        'previous_week' => 'Săptămâna anterioară',
        'next_week'     => 'Săptămâna viitoare',
        'scheduled'     => 'Întreținere programată',
        'scheduled_at'  => ', programată: timestamp',
        'status'        => [
            0 => 'Programată', // TODO: Hopefully remove this.
            1 => 'Sub investigație',
            2 => 'Identificat',
            3 => 'Sub observație',
            4 => 'Remediat',
        ],
    ],

    // Service Status
    'service' => [
        'good'  => '[0,1] System operational|[2,Inf] All systems are operational',
        'bad'   => '[0,1] The system is currently experiencing issues|[2,Inf] Some systems are experiencing issues',
        'major' => '[0,1] The service is experiencing a major outage|[2,Inf] Some systems are experiencing a major outage',
    ],

    'api' => [
        'regenerate' => 'Regenerează cheia API',
        'revoke'     => 'Revocă cheia API',
    ],

    // Metrics
    'metrics' => [
        'filter' => [
            'last_hour' => 'Last Hour',
            'hourly'    => 'Ultimele 12 ore',
            'weekly'    => 'Săptămână',
            'monthly'   => 'Lună',
        ],
    ],

    // Subscriber
    'subscriber' => [
        'subscribe' => 'Abonează-te pentru a primi cele mai recente actualizări',
        'button'    => 'Abonează-te',
        'manage'    => [
            'no_subscriptions' => 'You\'re currently subscribed to all updates.',
            'my_subscriptions' => 'You\'re currently subscribed to the following updates.',
        ],
        'email' => [
            'subscribe'          => 'Abonează-te la actualizări prin email.',
            'subscribed'         => 'Te-ai abonat la actualizări prin email, te rugăm să îți verifici adresa email și să confirmi abonarea.',
            'verified'           => 'Abonarea prin email a fost confirmată. Mulțumim!',
            'manage'             => 'Manage your subscription',
            'unsubscribe'        => 'Dezabonare de la actualizări prin email.',
            'unsubscribed'       => 'Actualizările prin email au fost anulate.',
            'failure'            => 'Ceva nu a funcționat în legătură cu abonarea.',
            'already-subscribed' => 'Cannot subscribe :email because they\'re already subscribed.',
            'verify'             => [
                'text'   => "Please confirm your email subscription to :app_name status updates.\n:link",
                'html'   => '<p>Please confirm your email subscription to :app_name status updates.</p>',
                'button' => 'Confirm Subscription',
            ],
            'maintenance' => [
                'subject' => '[Maintenance Scheduled] :name',
            ],
            'incident' => [
                'subject' => '[New Incident] :status: :name',
            ],
            'component' => [
                'subject'       => 'Component Status Update',
                'text'          => 'The component :component_name has seen a status change. The component is now at :component_human_status.\nThank you, :app_name',
                'html'          => '<p>The component :component_name has seen a status change. The component is now at :component_human_status.</p><p>Thank you, :app_name</p>',
                'tooltip-title' => 'Subscribe to notifications for :component_name.',
            ],
        ],
    ],

    'users' => [
        'email' => [
            'invite' => [
                'text' => "You have been invited to the team :app_name status page, to sign up follow the next link.\n:link\nThank you, :app_name",
                'html' => '<p>You have been invited to the team :app_name status page, to sign up follow the next link.</p><p><a href=":link">:link</a></p><p>Thank you, :app_name</p>',
            ],
        ],
    ],

    'signup' => [
        'title'    => 'Sign Up',
        'username' => 'Username',
        'email'    => 'Email',
        'password' => 'Password',
        'success'  => 'Your account has been created.',
        'failure'  => 'Something went wrong with the signup.',
    ],

    'system' => [
        'update' => 'There is a newer version of Cachet available. You can learn how to update <a href="https://docs.cachethq.io/docs/updating-cachet">here</a>!',
    ],

    // Modal
    'modal' => [
        'close'     => 'Close',
        'subscribe' => [
            'title'  => 'Subscribe to component updates',
            'body'   => 'Enter your email address to subscribe to updates for this component. If you\'re already subscribed, you\'ll already receive emails for this component.',
            'button' => 'Abonează-te',
        ],
    ],

    // Other
    'home'            => 'Home',
    'description'     => 'Stay up to date with the latest service updates from :app.',
    'powered_by'      => 'Powered by <a href="https://cachethq.io" class="links">Cachet</a>.',
    'about_this_site' => 'About This Site',
    'rss-feed'        => 'RSS',
    'atom-feed'       => 'Atom',
    'feed'            => 'Status Feed',

];
