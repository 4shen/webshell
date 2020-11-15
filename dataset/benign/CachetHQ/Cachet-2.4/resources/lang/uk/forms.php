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
        'email'            => 'Електронна пошта',
        'username'         => 'Ім’я користувача',
        'password'         => 'Пароль',
        'site_name'        => 'Назва сайту',
        'site_domain'      => 'Назва домену',
        'site_timezone'    => 'Оберіть Ваш часовий пояс',
        'site_locale'      => 'Оберіть свою мову',
        'enable_google2fa' => 'Ввімкнути двофакторну автентифікацію Google',
        'cache_driver'     => 'Драйвер кешування',
        'session_driver'   => 'Сессія драйверу',
    ],

    // Login form fields
    'login' => [
        'login'         => 'Ім\'я користувача або електронна пошта',
        'email'         => 'Електронна пошта',
        'password'      => 'Пароль',
        '2fauth'        => 'Код автентифікації',
        'invalid'       => 'Невірний логін чи пароль',
        'invalid-token' => 'Invalid token',
        'cookies'       => 'Ви повинні увімкнути куки щоб увійти.',
        'rate-limit'    => 'Rate limit exceeded.',
    ],

    // Incidents form fields
    'incidents' => [
        'name'               => 'Ім’я',
        'status'             => 'Статус',
        'component'          => 'Компонент',
        'message'            => 'Повідомлення',
        'message-help'       => 'Ви також можете використовувати Markdown.',
        'scheduled_at'       => 'When to schedule the maintenance for?',
        'incident_time'      => 'Коли цей інцидент відбувся?',
        'notify_subscribers' => 'Повідомити підписників?',
        'visibility'         => 'Incident Visibility',
        'public'             => 'Viewable by public',
        'logged_in_only'     => 'Only visible to logged in users',
        'templates'          => [
            'name'     => 'Ім’я',
            'template' => 'Template',
            'twig'     => 'Incident Templates can make use of the <a href="http://twig.sensiolabs.org/" target="_blank">Twig</a> templating language.',
        ],
    ],

    // Components form fields
    'components' => [
        'name'        => 'Ім’я',
        'status'      => 'Статус',
        'group'       => 'Group',
        'description' => 'Опис',
        'link'        => 'Посилання',
        'tags'        => 'Теги',
        'tags-help'   => 'Comma separated.',
        'enabled'     => 'Component enabled?',

        'groups' => [
            'name'               => 'Ім’я',
            'collapsing'         => 'Choose visibility of the group',
            'visible'            => 'Always expanded',
            'collapsed'          => 'Collapse the group by default',
            'collapsed_incident' => 'Collapse the group, but expand if there are issues',
        ],
    ],

    // Metric form fields
    'metrics' => [
        'name'             => 'Ім’я',
        'suffix'           => 'Suffix',
        'description'      => 'Опис',
        'description-help' => 'Ви також можете використовувати Markdown.',
        'display-chart'    => 'Display chart on status page?',
        'default-value'    => 'Default value',
        'calc_type'        => 'Calculation of metrics',
        'type_sum'         => 'Сума',
        'type_avg'         => 'В середньому',
        'places'           => 'Decimal places',
        'default_view'     => 'Типовий вигляд',
        'threshold'        => 'How many minutes of threshold between metric points?',

        'points' => [
            'value' => 'Значення',
        ],
    ],

    // Settings
    'settings' => [
        /// Application setup
        'app-setup' => [
            'site-name'              => 'Назва сайту',
            'site-url'               => 'URL сайту',
            'display-graphs'         => 'Відображати графіки на сторінці статусу?',
            'about-this-page'        => 'Інформація про цю сторінку',
            'days-of-incidents'      => 'How many days of incidents to show?',
            'banner'                 => 'Banner Image',
            'banner-help'            => "It's recommended that you upload files no bigger than 930px wide .",
            'subscribers'            => 'Allow people to signup to email notifications?',
            'automatic_localization' => 'Automatically localise your status page to your visitor\'s language?',
        ],
        'analytics' => [
            'analytics_google'       => 'Код Google Analytics',
            'analytics_gosquared'    => 'Код GoSquared Analytics',
            'analytics_piwik_url'    => 'URL of your Piwik instance (without http(s)://)',
            'analytics_piwik_siteid' => 'id сайту Piwik',
        ],
        'localization' => [
            'site-timezone'        => 'Часовий пояс сайту',
            'site-locale'          => 'Мова сайту',
            'date-format'          => 'формат дати',
            'incident-date-format' => 'Incident timestamp format',
        ],
        'security' => [
            'allowed-domains'      => 'Allowed domains',
            'allowed-domains-help' => 'Comma separated. The domain set above is automatically allowed by default.',
        ],
        'stylesheet' => [
            'custom-css' => 'Custom Stylesheet',
        ],
        'theme' => [
            'background-color'        => 'Background Color',
            'background-fills'        => 'Background fills (components, incidents, footer)',
            'banner-background-color' => 'Banner background color',
            'banner-padding'          => 'Banner padding',
            'fullwidth-banner'        => 'Enable fullwidth banner?',
            'text-color'              => 'Колір тексту',
            'dashboard-login'         => 'Show dashboard button in the footer?',
            'reds'                    => 'Red (used for errors)',
            'blues'                   => 'Blue (used for information)',
            'greens'                  => 'Green (used for success)',
            'yellows'                 => 'Yellow (used for alerts)',
            'oranges'                 => 'Orange (used for notices)',
            'metrics'                 => 'Metrics fill',
            'links'                   => 'Links',
        ],
    ],

    'user' => [
        'username'       => 'Ім’я користувача',
        'email'          => 'Електронна пошта',
        'password'       => 'Пароль',
        'api-token'      => 'API Token',
        'api-token-help' => 'Regenerating your API token will prevent existing applications from accessing Cachet.',
        'gravatar'       => 'Change your profile picture at Gravatar.',
        'user_level'     => 'User Level',
        'levels'         => [
            'admin' => 'Адміністратор',
            'user'  => 'Користувач',
        ],
        '2fa' => [
            'help' => 'Enabling two factor authentication increases security of your account. You will need to download <a href="https://support.google.com/accounts/answer/1066447?hl=en">Google Authenticator</a> or a similar app on to your mobile device. When you login you will be asked to provide a token generated by the app.',
        ],
        'team' => [
            'description' => 'Invite your team members by entering their email addresses here.',
            'email'       => 'Email #:id',
        ],
    ],

    // Buttons
    'add'    => 'додати',
    'save'   => 'Зберегти',
    'update' => 'Update',
    'create' => 'Створити',
    'edit'   => 'Edit',
    'delete' => 'Видалити',
    'submit' => 'Submit',
    'cancel' => 'Скасувати',
    'remove' => 'Remove',
    'invite' => 'Запросити',
    'signup' => 'Sign Up',

    // Other
    'optional' => '* Optional',
];
