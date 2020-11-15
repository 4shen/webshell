<?php

return [

    'company' => [
        'description'       => 'Promijenite naziv tvrtke, e-mail, adresu, porezni broj itd',
        'name'              => 'Naziv',
        'email'             => 'E-mail',
        'phone'             => 'Telefon',
        'address'           => 'Adresa',
        'logo'              => 'Logo',
    ],

    'localisation' => [
        'description'       => 'Postavite fiskalnu godinu, vremensku zonu, format datuma i više',
        'financial_start'   => 'Početak fiskalne godine',
        'timezone'          => 'Vremenska zona',
        'date' => [
            'format'        => 'Format datuma',
            'separator'     => 'Separator datuma',
            'dash'          => 'Crtica (-)',
            'dot'           => 'Točka (.)',
            'comma'         => 'Zarez (,)',
            'slash'         => 'Kosa crta (/)',
            'space'         => 'Razmak ( )',
        ],
        'percent' => [
            'title'         => 'Pozicija postotka (%)',
            'before'        => 'Ispred broja',
            'after'         => 'Nakon broja',
        ],
        'discount_location' => [
            'name'          => 'Discount Location',
            'item'          => 'At line',
            'total'         => 'At total',
            'both'          => 'Both line and total',
        ],
    ],

    'invoice' => [
        'description'       => 'Prilagodite prefiks fakture, broj, uvjete, podnožje itd',
        'prefix'            => 'Prefiks proja',
        'digit'             => 'Broj znamenki',
        'next'              => 'Sljedeći broj',
        'logo'              => 'Logo',
        'custom'            => 'Prilagođeno',
        'item_name'         => 'Ime stavke',
        'item'              => 'Stavke',
        'product'           => 'Proizvodi',
        'service'           => 'Usluge',
        'price_name'        => 'Naziv cijene',
        'price'             => 'Cijena',
        'rate'              => 'Stopa',
        'quantity_name'     => 'Naziv količine',
        'quantity'          => 'Količina',
        'payment_terms'     => 'Uvjeti plaćanja',
        'title'             => 'Naslov',
        'subheading'        => 'Podnaslov',
        'due_receipt'       => 'Rok za primanje',
        'due_days'          => 'Rok dospijeća: nekoliko dana',
        'choose_template'   => 'Odaberite drugi predložak',
        'default'           => 'Zadano',
        'classic'           => 'Klasično',
        'modern'            => 'Moderno',
    ],

    'default' => [
        'description'       => 'Zadani račun, valuta, jezik vaše tvrtke',
        'list_limit'        => 'Zapisa po stranici',
        'use_gravatar'      => 'Koristi Gravatar',
    ],

    'email' => [
        'description'       => 'Promijenite protokol za slanje i e-mail predloške',
        'protocol'          => 'Protokol',
        'php'               => 'PHP Mail',
        'smtp' => [
            'name'          => 'SMTP',
            'host'          => 'SMTP Host',
            'port'          => 'SMTP Port',
            'username'      => 'SMTP Korisničko Ime',
            'password'      => 'SMTP Lozinka',
            'encryption'    => 'SMTP sigurnost',
            'none'          => 'Ništa',
        ],
        'sendmail'          => 'Sendmail',
        'sendmail_path'     => 'Sendmail putanja',
        'log'               => 'E-mail evidentiranje',

        'templates' => [
            'subject'                   => 'Predmet',
            'body'                      => 'Sadržaj',
            'tags'                      => '<strong>Dostupne oznake:</strong> :tag_list',
            'invoice_new_customer'      => 'Predložak primljenog plaćanja (poslano kupcu)',
            'invoice_remind_customer'   => 'Predložak podsjetnika za fakturu (poslano kupcu)',
            'invoice_remind_admin'      => 'Predložak podsjetnika za fakturu (poslan administratoru)',
            'invoice_recur_customer'    => 'Predložak ponavljajućeg računa (poslano kupcu)',
            'invoice_recur_admin'       => 'Predložak ponavljajućeg računa (poslano administratoru)',
            'invoice_payment_customer'  => 'Predložak primljenog plaćanja (poslano kupcu)',
            'invoice_payment_admin'     => 'Predložak primljenog plaćanja (poslano administratoru)',
            'bill_remind_admin'         => 'Predložak podsjetnika za račun (poslano administratoru)',
            'bill_recur_admin'          => 'Ponavljajući predložak računa (poslan administratoru)',
        ],
    ],

    'scheduling' => [
        'name'              => 'Zakazivanje',
        'description'       => 'Automatski podsjetnici i naredba za ponavljanje',
        'send_invoice'      => 'Slanje podsjetnika faktura',
        'invoice_days'      => 'Slanje prije datuma dospijeća',
        'send_bill'         => 'Slanje podsjetnika računa',
        'bill_days'         => 'Slanje prije datuma dospijeća',
        'cron_command'      => 'Cron naredba',
        'schedule_time'     => 'Vrijeme pokretanja',
    ],

    'categories' => [
        'description'       => 'Neograničene kategorije za prihod, rashod i stavke',
    ],

    'currencies' => [
        'description'       => 'Kreirajte i upravljajte valutama i postavite njihove tečajeve',
    ],

    'taxes' => [
        'description'       => 'Fiksne, normalne, uključive i složene porezne stope',
    ],

];
