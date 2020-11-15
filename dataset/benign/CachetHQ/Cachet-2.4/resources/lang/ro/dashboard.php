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

    'dashboard'          => 'Panou de control',
    'writeable_settings' => 'The Cachet settings directory is not writeable. Please make sure that <code>./bootstrap/cachet</code> is writeable by the web server.',

    // Incidents
    'incidents' => [
        'title'                    => 'Incidents &amp; Schedule',
        'incidents'                => 'Incidente',
        'logged'                   => '{0} Nu sunt incidente, bravo! | Ai adăugat un incident. | Ai raportat <strong>:count</strong> incidente.',
        'incident-create-template' => 'Crează şablon',
        'incident-templates'       => 'Şabloane incident',
        'updates'                  => '{0} Nicio actualizare|O actualizare|:count Actualizări',
        'add'                      => [
            'title'   => 'Raportează un incident',
            'success' => 'Incidentul a fost adăugat.',
            'failure' => 'A avut loc o eroare la adăugarea incidentului, vă rugăm să încercaţi din nou.',
        ],
        'edit' => [
            'title'   => 'Editează un incident',
            'success' => 'Incidentul a fost actualizat.',
            'failure' => 'A avut loc o eroare la actualizarea incidentului, vă rugăm să încercaţi din nou.',
        ],
        'delete' => [
            'success' => 'Incidentul a fost şters şi nu va mai apărea pe pagina de status.',
            'failure' => 'Incidentul nu a putut fi şters, vă rugăm încercaţi din nou.',
        ],
        'update' => [
            'title'    => 'Adaugă o nouă actualizare a incidentului',
            'subtitle' => 'Adaugă o actualizare la <strong>:incident</strong>',
        ],

        // Incident templates
        'templates' => [
            'title' => 'Şabloane incident',
            'add'   => [
                'title'   => 'Adaugă un nou şablon pentru incident',
                'message' => 'Trebuie să adaugi un şablon pentru incident.',
                'success' => 'Noul şablon pentru incident a fost creat.',
                'failure' => 'Ceva nu a funcționat legat de șablonul incidentului.',
            ],
            'edit' => [
                'title'   => 'Editare Şablon',
                'success' => 'Şablonul incidentului a fost actualizat.',
                'failure' => 'Ceva nu a funcționat legat de actualizarea șablonului incidentului',
            ],
            'delete' => [
                'success' => 'Şablonul incidentului a fost şters.',
                'failure' => 'Şablonul incidentului nu a putut fi şters, vă rugăm încercaţi din nou.',
            ],
        ],
    ],

    // Incident Maintenance
    'schedule' => [
        'schedule'     => 'Întreținere programată',
        'logged'       => '{0} Nu există programări, bravo.|Ai o singură programare.|Ai <strong>:count</strong> programări.',
        'scheduled_at' => 'Programat la :timestamp',
        'add'          => [
            'title'   => 'Adăugaţi Programare Mentenanţă',
            'success' => 'Programare adăugată.',
            'failure' => 'Ceva nu a funcționat legat de adăugarea unei programări, vă rugăm încercați din nou.',
        ],
        'edit' => [
            'title'   => 'Modificați Programarea Mentenanței',
            'success' => 'Programarea a fost actualizată!',
            'failure' => 'Ceva nu a funcționat legat de modificarea programării, vă rugăm încercați din nou.',
        ],
        'delete' => [
            'success' => 'Programarea mentenanței a fost ștearsă și nu va mai apărea pe pagina dvs. de stare.',
            'failure' => 'Programarea mentenanței nu a putut fi ștearsă, vă rugăm încercați din nou.',
        ],
    ],

    // Components
    'components' => [
        'components'         => 'Componente',
        'component_statuses' => 'Stările Componentei',
        'listed_group'       => 'Grupat ca :name',
        'add'                => [
            'title'   => 'Adăugați o componentă',
            'message' => 'Ar trebui să adăugați o componentă.',
            'success' => 'Componenta a fost creată.',
            'failure' => 'Ceva nu a funcționat legat de grupul de componente, vă rugăm încercați din nou.',
        ],
        'edit' => [
            'title'   => 'Modificați o componentă',
            'success' => 'Componenta a fost actualizată.',
            'failure' => 'Ceva nu a funcționat legat de grupul de componente, vă rugăm încercați din nou.',
        ],
        'delete' => [
            'success' => 'Componenta a fost ștearsă!',
            'failure' => 'Component nu a putut fi ștearsă, vă rugăm încercați din nou.',
        ],

        // Component groups
        'groups' => [
            'groups'        => 'Group de componente|Grupuri de componente',
            'no_components' => 'Ar trebui să adăugați un grup de componente.',
            'add'           => [
                'title'   => 'Adăugați un grup de componente',
                'success' => 'Grupul de componente a fost adăugat.',
                'failure' => 'Ceva nu a funcționat legat de grupul de componente, vă rugăm încercați din nou.',
            ],
            'edit' => [
                'title'   => 'Modificați un grup de componente',
                'success' => 'Grupul de componente a fost actualizat.',
                'failure' => 'Ceva nu a funcționat legat de grupul de componente, vă rugăm încercați din nou.',
            ],
            'delete' => [
                'success' => 'Grupul de componente a fost șters!',
                'failure' => 'Grupul de componente nu a putut fi șters, vă rugăm încercați din nou.',
            ],
        ],
    ],

    // Metrics
    'metrics' => [
        'metrics' => 'Măsurători',
        'add'     => [
            'title'   => 'Adaugă o măsurătoare',
            'message' => 'Ar trebui să adaugi o măsurătoare.',
            'success' => 'Măsurătoarea a fost creată.',
            'failure' => 'Ceva nu a funcționat legat de măsurătoare, vă rugăm încercați din nou.',
        ],
        'edit' => [
            'title'   => 'Modificați o măsurătoare',
            'success' => 'Măsurătoarea a fost actualizată.',
            'failure' => 'Ceva nu a funcționat legat de măsurătoare, vă rugăm încercați din nou.',
        ],
        'delete' => [
            'success' => 'Măsurătoarea a fost ștearsă și nu va mai apărea pe pagina dvs. de stare.',
            'failure' => 'Măsurătoarea nu a putut fi ștearsă, vă rugăm încercați din nou.',
        ],
    ],
    // Subscribers
    'subscribers' => [
        'subscribers'      => 'Abonați',
        'description'      => 'Abonații vor primi actualizări prin email când incidente noi sunt adăugate sau componentele sunt actualizate.',
        'verified'         => 'Verificat',
        'not_verified'     => 'Neverificat',
        'subscriber'       => ':email, abonat la :date',
        'no_subscriptions' => 'Ați fost abonat la toate actualizările',
        'add'              => [
            'title'   => 'Adaugă un nou abonat',
            'success' => 'Abonatul a fost adăugat!',
            'failure' => 'Ceva nu a funcționat legat de adăugarea abonatului, vă rugăm încercați din nou.',
            'help'    => 'Introduceți fiecare abonat pe o nouă linie.',
        ],
        'edit' => [
            'title'   => 'Actualizează abonatul',
            'success' => 'Abonatul a fost actualizat!',
            'failure' => 'Ceva nu a funcționat legat de modificarea abonatului, vă rugăm încercați din nou.',
        ],
    ],

    // Team
    'team' => [
        'team'        => 'Echipa',
        'member'      => 'Membru',
        'profile'     => 'Profil',
        'description' => 'Team Members will be able to add, modify &amp; edit components and incidents.',
        'add'         => [
            'title'   => 'Adaugă un nou membru la echipă',
            'success' => 'Un nou membru a fost adăugat la echipă.',
            'failure' => 'Un nou membru nu a putut fi adăugat la echipă, vă rugăm încercați din nou.',
        ],
        'edit' => [
            'title'   => 'Actualizați profilul',
            'success' => 'Profilul a fost actualizat.',
            'failure' => 'Ceva nu a funcționat legat de actualizarea profilului, vă rugăm încercați din nou.',
        ],
        'delete' => [
            'success' => 'Membrul echipei a fost șters și nu va mai avea acces la panoul de control!',
            'failure' => 'Un nou membru nu a putut fi adăugat la echipă, vă rugăm încercați din nou.',
        ],
        'invite' => [
            'title'   => 'Invită un nou membru în echipă',
            'success' => 'Invitația a fost trimisă',
            'failure' => 'Invitația nu a putut fi trimisă, vă rugăm încercați din nou.',
        ],
    ],

    // Settings
    'settings' => [
        'settings'  => 'Setări',
        'app-setup' => [
            'app-setup'   => 'Instalarea Aplicației',
            'images-only' => 'Puteți urca doar imagini.',
            'too-big'     => 'Fișierul urcat este prea mare. Urcați o imagine mai mică de :size',
        ],
        'analytics' => [
            'analytics' => 'Analize',
        ],
        'log' => [
            'log' => 'Jurnal',
        ],
        'localization' => [
            'localization' => 'Localizare',
        ],
        'customization' => [
            'customization' => 'Personalizare',
            'header'        => 'Header HTML Personalizat',
            'footer'        => 'Footer HTML Personalizat',
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
            'security'   => 'Securitate',
            'two-factor' => 'Utilizatori care nu folosesc autentificare two-factor',
        ],
        'stylesheet' => [
            'stylesheet' => 'Listă de stiluri',
        ],
        'theme' => [
            'theme' => 'Tema',
        ],
        'edit' => [
            'success' => 'Setările au fost salvate.',
            'failure' => 'Setările nu au putut fi salvate.',
        ],
        'credits' => [
            'credits'       => 'Autori',
            'contributors'  => 'Contribuitori',
            'license'       => 'Cachet este un proiect open source cu licență BSD-3, realizat de <a href="https://alt-three.com/?utm_source=cachet&utm_medium=credits&utm_campaign=Cachet%20Credit%20Dashboard" target="_blank">Alt Three Services Limited</a>.',
            'backers-title' => 'Susținători & Sponsori',
            'backers'       => 'Dacă vreți să susțineți dezvoltarea proiectului, vizitați pagina de campanie <a href="https://patreon.com/jbrooksuk" target="_blank">Cachet Patreon</a>.',
            'thank-you'     => 'Mulțumim tuturor celor :count contribuitori.',
        ],
    ],

    // Login
    'login' => [
        'login'      => 'Autentificare',
        'logged_in'  => 'Ești autentificat.',
        'welcome'    => 'Bine ai revenit!',
        'two-factor' => 'Te rog introdu token-ul tău.',
    ],

    // Sidebar footer
    'help'        => 'Ajutor',
    'status_page' => 'Pagina de Stare',
    'logout'      => 'Deconectează-te',

    // Notifications
    'notifications' => [
        'notifications' => 'Notificări',
        'awesome'       => 'Minunat.',
        'whoops'        => 'Hopa.',
    ],

    // Widgets
    'widgets' => [
        'support'          => 'Susțineți Cachet',
        'support_subtitle' => 'Vizitați pagina noastră pe <strong><a href="https://patreon.com/jbrooksuk" target="_blank">Patreon</a></strong>!',
        'news'             => 'Ultimele Știri',
        'news_subtitle'    => 'Obţineţi cea mai recentă actualizare',
    ],

    // Welcome modal
    'welcome' => [
        'welcome' => 'Bun venit pe noua pagină de Stare!',
        'message' => 'Pagina de stare este aproape gata! S-ar putea să doriți să configurați aceste setări suplimentare',
        'close'   => 'Du-mă direct la panoul de control',
        'steps'   => [
            'component'  => 'Creează componente',
            'incident'   => 'Creează incidente',
            'customize'  => 'Personalizează',
            'team'       => 'Adaugă utilizatori',
            'api'        => 'Generează un token pentru API',
            'two-factor' => 'Autentificare Two Factor',
        ],
    ],

];
