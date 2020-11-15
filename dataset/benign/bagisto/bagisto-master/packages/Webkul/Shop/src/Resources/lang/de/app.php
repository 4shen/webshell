<?php

return [
    'invalid_vat_format' => 'Die angegebene Umsatzsteuer-ID hat ein falsches Format',
    'security-warning' => 'Verdächtige Aktivität gefunden!!!',
    'nothing-to-delete' => 'Nichts zu löschen',

    'layouts' => [
        'my-account' => 'Mein Konto',
        'profile' => 'Profil',
        'address' => 'Adresse',
        'reviews' => 'Bewertungen',
        'wishlist' => 'Wunschliste',
        'orders' => 'Bestellungen',
        'downloadable-products' => 'Herunterladbare Produkte'
    ],

    'common' => [
        'error' => 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.',
        'no-result-found' => 'Wir konnten keine Aufzeichnungen finden.'
    ],

    'home' => [
        'page-title' => config('app.name') . ' - Start',
        'featured-products' => 'Ausgewählte Produkte',
        'new-products' => 'Neue Produkte',
        'verify-email' => 'Bestätigen Sie Ihr E-Mail-Konto',
        'resend-verify-email' => 'Bestätigungsmail erneut senden'
    ],

    'header' => [
        'title' => 'Konto',
        'dropdown-text' => 'Warenkorb, Bestellungen und Wunschliste verwalten',
        'sign-in' => 'Anmelden',
        'sign-up' => 'Registrieren',
        'account' => 'Konto',
        'cart' => 'Warenkorb',
        'profile' => 'Profil',
        'wishlist' => 'Wunschliste',
        'cart' => 'Warenkorb',
        'logout' => 'Ausloggen',
        'search-text' => 'Nach Produkten suchen'
    ],

    'minicart' => [
        'view-cart' => 'Warenkorb ansehen',
        'checkout' => 'Bestellen',
        'cart' => 'Warenkorb',
        'zero' => '0'
    ],

    'footer' => [
        'subscribe-newsletter' => 'Newsletter abonnieren',
        'subscribe' => 'Abonnieren',
        'locale' => 'Sprache',
        'currency' => 'Währung',
    ],

    'subscription' => [
        'unsubscribe' => 'Abmelden',
        'subscribe' => 'Abonnieren',
        'subscribed' => 'Sie haben jetzt Abonnement-E-Mails abonniert.',
        'not-subscribed' => 'Sie können keine Abonnement-E-Mails abonnieren. Versuchen Sie es später erneut.',
        'already' => 'Sie haben unsere Abonnementliste bereits abonniert.',
        'unsubscribed' => 'Sie werden von Abonnement-Mails abgemeldet.',
        'already-unsub' => 'Sie sind bereits abgemeldet.',
        'not-subscribed' => 'Error! E-Mails können derzeit nicht gesendet werden. Bitte versuchen Sie es später erneut.'
    ],

    'search' => [
        'no-results' => 'Keine Ergebnisse gefunden',
        'page-title' => config('app.name') . ' - Suchen',
        'found-results' => 'Suchergebnisse gefunden',
        'found-result' => 'Suchergebnis gefunden',
        'analysed-keywords' => 'Analysed Keywords'
    ],

    'reviews' => [
        'title' => 'Titel',
        'add-review-page-title' => 'Bewertung hinzufügen',
        'write-review' => 'Bewertung schreiben',
        'review-title' => 'Geben Sie Ihrer Bewertung einen Titel',
        'product-review-page-title' => 'Produktbewertung',
        'rating-reviews' => 'Sterne & Bewertungen',
        'submit' => 'EINREICHEN',
        'delete-all' => 'Alle Bewertungen wurden erfolgreich gelöscht',
        'ratingreviews' => ':rating Sterne & :review Bewertungen',
        'star' => 'Sterne',
        'percentage' => ':percentage %',
        'id-star' => 'Sterne',
        'name' => 'Name',
    ],

    'customer' => [
        'signup-text' => [
            'account_exists' => 'Sie haben bereits ein Konto',
            'title' => 'Anmelden'
        ],

        'signup-form' => [
            'page-title' => 'Neues Kundenkonto erstellen',
            'title' => 'Anmelden',
            'firstname' => 'Vorname',
            'lastname' => 'Nachname',
            'email' => 'E-Mail',
            'password' => 'Passwort',
            'confirm_pass' => 'Passwort bestätigen',
            'button_title' => 'Registrieren',
            'agree' => 'Zustimmen',
            'terms' => 'Regeln',
            'conditions' => 'Bedigungen',
            'using' => 'durch die Nutzung dieser Website',
            'agreement' => 'Zustimmung',
            'success' => 'Konto erfolgreich erstellt.',
            'success-verify' => 'Konto erfolgreich erstellt, eine E-Mail zur Bestätigung wurde versendet.',
            'success-verify-email-unsent' => 'Das Konto wurde erfolgreich erstellt, aber die Bestätigungs-E-Mail wurde nicht ordnungsgemäß gesendet. Zur Bestätigung wurde eine E-Mail gesendet.',
            'failed' => 'Error! Sie können Ihr Konto nicht erstellen. Bitte versuchen Sie es später erneut.',
            'already-verified' => 'Ihr Konto ist bereits verifiziert. Oder versuchen Sie erneut, eine neue Bestätigungs-E-Mail zu senden.',
            'verification-not-sent' => 'Error! Problem beim Senden einer Bestätigungs-E-Mail, versuchen Sie es später erneut.',
            'verification-sent' => 'Bestätigungs-E-Mail gesendet',
            'verified' => 'Ihr Konto wurde verifiziert. Versuchen Sie jetzt, sich anzumelden.',
            'verify-failed' => 'Wir können Ihr E-Mail-Konto nicht bestätigen.',
            'dont-have-account' => 'Sie haben kein Konto bei uns.',
            'customer-registration' => 'Kunde erfolgreich registriert'
        ],

        'login-text' => [
            'no_account' => 'Sie haben noch keinen Account',
            'title' => 'Registrieren',
        ],

        'login-form' => [
            'page-title' => 'Kundenlogin',
            'title' => 'Anmelden',
            'email' => 'E-Mail',
            'password' => 'Passwort',
            'forgot_pass' => 'Passwort vergessen?',
            'button_title' => 'Anmelden',
            'remember' => 'Angemeldet bleiben',
            'footer' => '© Copyright :year Webkul Software, All rights reserved',
            'invalid-creds' => 'Bitte überprüfen Sie Ihre Anmeldeinformationen und versuchen Sie es erneut.',
            'verify-first' => 'Bestätigung Sie zuerst Ihr E-Mail-Konto.',
            'not-activated' => 'Ihre Aktivierung erfordert die Genehmigung des Administrators',
            'resend-verification' => 'Senden Sie die Bestätigungsmail erneut'
        ],

        'forgot-password' => [
            'title' => 'Passwort wiederherstellen',
            'email' => 'E-Mail',
            'submit' => 'E-Mail zum Zurücksetzen des Passworts senden',
            'page_title' => 'Haben Sie Ihr Passwort vergessen ?'
        ],

        'reset-password' => [
            'title' => 'Passwort zurücksetzen',
            'email' => 'Registrierte E-Mail',
            'password' => 'Passwort',
            'confirm-password' => 'Passwort bestätigen',
            'back-link-title' => 'Zurück zur Anmeldung',
            'submit-btn-title' => 'Passwort zurücksetzen'
        ],

        'account' => [
            'dashboard' => 'Profil bearbeiten',
            'menu' => 'Menu',

            'profile' => [
                'index' => [
                    'page-title' => 'Profil',
                    'title' => 'Profil',
                    'edit' => 'Bearbeiten',
                ],

                'edit-success' => 'Profil erfolgreich aktualisiert.',
                'edit-fail' => 'Error! Das Profil kann nicht aktualisiert werden. Bitte versuchen Sie es später erneut.',
                'unmatch' => 'Das alte Passwort stimmt nicht überein.',

                'fname' => 'Vorname',
                'lname' => 'Nachname',
                'gender' => 'Geschlecht',
                'other' => 'Andere',
                'male' => 'Männlich',
                'female' => 'weiblich',
                'dob' => 'Geburtsdatum',
                'phone' => 'Telefon',
                'email' => 'E-Mail',
                'opassword' => 'Altes Passwort',
                'password' => 'Passwort',
                'cpassword' => 'Passwort bestätigen',
                'submit' => 'Profil aktualisieren',

                'edit-profile' => [
                    'title' => 'Profil bearbeiten',
                    'page-title' => 'Profilformular bearbeiten'
                ]
            ],

            'address' => [
                'index' => [
                    'page-title' => 'Adresse',
                    'title' => 'Adresse',
                    'add' => 'Adresse hinzufügen',
                    'edit' => 'Bearbeiten',
                    'empty' => 'Sie haben hier keine gespeicherten Adressen. Bitte versuchen Sie, diese zu erstellen, indem Sie auf den unten stehenden Link klicken',
                    'create' => 'Adresse erstellen',
                    'delete' => 'Löschen',
                    'make-default' => 'Standard hinzufügen',
                    'default' => 'Standard',
                    'contact' => 'Kontakt',
                    'confirm-delete' =>  'Möchten Sie diese Adresse wirklich löschen?',
                    'default-delete' => 'Die Standardadresse kann nicht geändert werden.',
                    'enter-password' => 'Geben Sie Ihr Passwort ein.',
                ],

                'create' => [
                    'page-title' => 'Adressformular hinzufügen',
                    'company_name' => 'Name der Firma',
                    'first_name' => 'Vorname',
                    'last_name' => 'Nachname',
                    'vat_id' => 'Umsatzsteuer-ID',
                    'vat_help_note' => '[Hinweis: Verwenden Sie den Ländercode mit der Umsatzsteuer-Identifikationsnummer. Z.B. INV01234567891]',
                    'title' => 'Adresse hinzufügen',
                    'street-address' => 'Straße',
                    'country' => 'Land',
                    'state' => 'Bundesland',
                    'select-state' => 'Wählen Sie eine Region, ein Bundesland oder eine Provinz aus',
                    'city' => 'Stadt',
                    'postcode' => 'Postleitzahl',
                    'phone' => 'Telefon',
                    'submit' => 'Adresse speichern',
                    'success' => 'Adresse wurde erfolgreich hinzugefügt.',
                    'error' => 'Adresse kann nicht hinzugefügt werden.'
                ],

                'edit' => [
                    'page-title' => 'Adresse bearbeiten',
                    'company_name' => 'Name der Firma',
                    'first_name' => 'Vorname',
                    'last_name' => 'Nachname',
                    'vat_id' => 'Umsatzsteuer-ID',
                    'title' => 'Adresse bearbeiten',
                    'street-address' => 'Straße',
                    'submit' => 'Adresse speichern',
                    'success' => 'Adresse erfolgreich aktualisiert.',
                ],
                'delete' => [
                    'success' => 'Adresse erfolgreich gelöscht',
                    'failure' => 'Adresse kann nicht gelöscht werden',
                    'wrong-password' => 'Falsches Passwort !'
                ]
            ],

            'order' => [
                'index' => [
                    'page-title' => 'Bestellungen',
                    'title' => 'Bestellungen',
                    'order_id' => 'Auftragsnummer',
                    'date' => 'Datum',
                    'status' => 'Status',
                    'total' => 'Gesamt',
                    'order_number' => 'Bestellnummer',
                    'processing' => 'Wird bearbeitet',
                    'completed' => 'Abgeschlossen',
                    'canceled' => 'Abgebrochen',
                    'closed' => 'Geschlossen',
                    'pending' => 'Ausstehend',
                    'pending-payment' => 'Ausstehende Zahlung',
                    'fraud' => 'Betrug'
                ],

                'view' => [
                    'page-tile' => 'Bestellung #:order_id',
                    'info' => 'Informationen',
                    'placed-on' => 'Vom',
                    'products-ordered' => 'Bestellte Produkte',
                    'invoices' => 'Rechnungen',
                    'shipments' => 'Sendungen',
                    'SKU' => 'SKU',
                    'product-name' => 'Name',
                    'qty' => 'Menge',
                    'item-status' => 'Artikelstatus',
                    'item-ordered' => 'Bestellt (:qty_ordered)',
                    'item-invoice' => 'In Rechnung gestellt (:qty_invoiced)',
                    'item-shipped' => 'Versendet (:qty_shipped)',
                    'item-canceled' => 'Abgebrochen (:qty_canceled)',
                    'item-refunded' => 'Rückerstattet (:qty_refunded)',
                    'price' => 'Preis',
                    'total' => 'Gesamt',
                    'subtotal' => 'Zwischensumme',
                    'shipping-handling' => 'Versand & Bearbeitung',
                    'tax' => 'Umsatzsteuer',
                    'discount' => 'Rabatt',
                    'tax-percent' => 'Umsatzsteuerprozent',
                    'tax-amount' => 'Umsatzsteuerbetrag',
                    'discount-amount' => 'Rabattbetrag',
                    'grand-total' => 'Gesamtsumme',
                    'total-paid' => 'Insgesamt bezahlt',
                    'total-refunded' => 'Insgesamt erstattet',
                    'total-due' => 'Insgesamt fällig',
                    'shipping-address' => 'Lieferanschrift',
                    'billing-address' => 'Rechnungsadresse',
                    'shipping-method' => 'Versandart',
                    'payment-method' => 'Zahlungsmethode',
                    'individual-invoice' => 'Rechnung #:invoice_id',
                    'individual-shipment' => 'Sendung #:shipment_id',
                    'print' => 'Drucken',
                    'invoice-id' => 'Rechnungsnummer',
                    'order-id' => 'Auftragsnummer',
                    'order-date' => 'Bestelldatum',
                    'bill-to' => 'Rechnung an',
                    'ship-to' => 'Versenden an',
                    'contact' => 'Kontakt',
                    'refunds' => 'Rückerstattungen',
                    'individual-refund' => 'Rückerstattung #:refund_id',
                    'adjustment-refund' => 'Anpassungsrückerstattung',
                    'adjustment-fee' => 'Anpassungsgebühr',
                ]
            ],

            'wishlist' => [
                'page-title' => 'Wunschliste',
                'title' => 'Wunschliste',
                'deleteall' => 'Alles löschen',
                'moveall' => 'Alle Produkte zum Warenkorb hinzufügen',
                'move-to-cart' => 'In den Warenkorb legen',
                'error' => 'Das Produkt kann aufgrund unbekannter Probleme nicht zur Wunschliste hinzugefügt werden. Bitte versuchen Sie es später erneut',
                'add' => 'Artikel erfolgreich zur Wunschliste hinzugefügt',
                'remove' => 'Artikel erfolgreich von der Wunschliste entfernt',
                'moved' => 'Artikel erfolgreich in den Warenkorb verschoben',
                'option-missing' => 'Produktoptionen fehlen, sodass Artikel nicht auf die Wunschliste verschoben werden können.',
                'move-error' => 'Artikel kann nicht auf die Wunschliste verschoben werden. Bitte versuchen Sie es später erneut',
                'success' => 'Artikel erfolgreich zur Wunschliste hinzugefügt',
                'failure' => 'Artikel kann nicht zur Wunschliste hinzugefügt werden. Bitte versuchen Sie es später erneut',
                'already' => 'Artikel bereits in Ihrer Wunschliste vorhanden',
                'removed' => 'Artikel erfolgreich von der Wunschliste entfernt',
                'remove-fail' => 'Artikel kann nicht von der Wunschliste entfernt werden. Bitte versuchen Sie es später erneut',
                'empty' => 'Sie haben keine Artikel auf Ihrer Wunschliste',
                'remove-all-success' => 'Alle Artikel von Ihrer Wunschliste wurden entfernt',
            ],

            'downloadable_products' => [
                'title' => 'Herunterladbare Produkte',
                'order-id' => 'Auftragsnummer',
                'date' => 'Datum',
                'name' => 'Titel',
                'status' => 'Status',
                'pending' => 'Ausstehend',
                'available' => 'Verfügbar',
                'expired' => 'Abgelaufen',
                'remaining-downloads' => 'Verbleibende Downloads',
                'unlimited' => 'Unbegrenzt',
                'download-error' => 'Der Download-Link ist abgelaufen.'
            ],

            'review' => [
                'index' => [
                    'title' => 'Bewertungen',
                    'page-title' => 'Bewertungen'
                ],

                'view' => [
                    'page-tile' => 'Bewertung #:id',
                ]
            ]
        ]
    ],

    'products' => [
        'layered-nav-title' => 'Einkaufen bei',
        'price-label' => 'Angebotspreis von',
        'remove-filter-link-title' => 'Alles löschen',
        'filter-to' => 'bis',
        'sort-by' => 'Sortieren',
        'from-a-z' => 'Von A-Z',
        'from-z-a' => 'Von Z-A',
        'newest-first' => 'Neuste zuerst',
        'oldest-first' => 'Älteste zuerst',
        'cheapest-first' => 'Günstigste zuerst',
        'expensive-first' => 'Teuerste zuerst',
        'show' => 'Anzeigen',
        'pager-info' => 'Zeige :showing von :total Artikeln',
        'description' => 'Beschreibung',
        'specification' => 'Spezifikation',
        'total-reviews' => ':total Bewertungen',
        'total-rating' => ':total_rating Sterne & :total_reviews Bewertungen',
        'by' => 'Durch :name',
        'up-sell-title' => 'Wir haben andere Produkte gefunden, die Ihnen gefallen könnten!',
        'related-product-title' => 'Verwandte Produkte',
        'cross-sell-title' => 'Mehr Auswahl',
        'reviews-title' => 'Sterne & Bewertungen',
        'write-review-btn' => 'Eine Bewertung schreiben',
        'choose-option' => 'Wähle eine Option',
        'sale' => 'Verkauf',
        'new' => 'Neu',
        'empty' => 'Keine Produkte in dieser Kategorie verfügbar',
        'add-to-cart' => 'In Warenkorb',
        'book-now' => 'buchen Sie jetzt',
        'buy-now' => 'Jetzt kaufen',
        'whoops' => 'Hoppla!',
        'quantity' => 'Menge',
        'in-stock' => 'Auf Lager',
        'out-of-stock' => 'Nicht vorrättig',
        'view-all' => 'Alle ansehen',
        'select-above-options' => 'Bitte wählen Sie zuerst die oben genannten Optionen aus.',
        'less-quantity' => 'Die Menge kann nicht kleiner als eins sein.',
        'samples' => 'Beispiele',
        'links' => 'Links',
        'sample' => 'Beispiel',
        'name' => 'Name',
        'qty' => 'Menge',
        'starting-at' => 'Beginnt um',
        'customize-options' => 'Optionen anpassen',
        'choose-selection' => 'Wählen Sie eine Auswahl',
        'your-customization' => 'Ihre Anpassung',
        'total-amount' => 'Gesamtmenge',
        'none' => 'Keine',
        'available' => 'Verfügbar',
        'settings' => 'Settings',
        'compare_options' => 'Compare Options',
    ],

    // 'reviews' => [
    //     'empty' => 'Sie haben noch kein Produkt bewertet'
    // ]

    'buynow' => [
        'no-options' => 'Bitte wählen Sie Optionen aus, bevor Sie dieses Produkt kaufen.'
    ],

    'checkout' => [
        'cart' => [
            'integrity' => [
                'missing_fields' => 'Einige erforderliche Felder für dieses Produkt fehlen.',
                'missing_options' => 'Für dieses Produkt fehlen Optionen.',
                'missing_links' => 'Für dieses Produkt fehlen herunterladbare Links.',
                'qty_missing' => 'Mindestens ein Produkt sollte mehr als 1 Menge enthalten.',
                'qty_impossible' => 'Es kann nicht mehr als eines dieser Produkte zum Warenkorb hinzugefügt werden.'
            ],
            'create-error' => 'Beim Erstellen des Warenkorbs ist ein Problem aufgetreten.',
            'title' => 'Warenkorb',
            'empty' => 'Ihr Einkaufswagen ist leer',
            'update-cart' => 'Warenkorb aktualisieren',
            'continue-shopping' => 'Mit dem Einkaufen fortfahren',
            'proceed-to-checkout' => 'Zur Kasse',
            'remove' => 'Entfernen',
            'remove-link' => 'Entfernen',
            'move-to-wishlist' => 'Zur Wunschliste verschieben',
            'move-to-wishlist-success' => 'Artikel wurde erfolgreich auf die Wunschliste verschoben.',
            'move-to-wishlist-error' => 'Das Objekt kann nicht auf die Wunschliste verschoben werden. Bitte versuchen Sie es später erneut.',
            'add-config-warning' => 'Bitte wählen Sie die Option, bevor Sie sie zum Warenkorb hinzufügen.',
            'quantity' => [
                'quantity' => 'Menge',
                'success' => 'Warenkorbartikel erfolgreich aktualisiert.',
                'illegal' => 'Die Menge kann nicht kleiner als eins sein.',
                'inventory_warning' => 'Die angeforderte Menge ist nicht verfügbar. Bitte versuchen Sie es später erneut.',
                'error' => 'Die Elemente können derzeit nicht aktualisiert werden. Bitte versuchen Sie es später erneut.'
            ],

            'item' => [
                'error_remove' => 'Keine Artikel aus dem Warenkorb zu entfernen',
                'success' => 'Artikel wurde erfolgreich zum Warenkorb hinzugefügt',
                'success-remove' => 'Artikel wurde erfolgreich aus dem Warenkorb entfernt',
                'error-add' => 'Artikel kann nicht zum Warenkorb hinzugefügt werden. Bitte versuchen Sie es später erneut',
            ],
            'quantity-error' => 'Die angeforderte Menge ist nicht verfügbar',
            'cart-subtotal' => 'Warenkorb Zwischensumme',
            'cart-remove-action' => 'Wollen Sie dies wirklich tun?',
            'partial-cart-update' => 'Nur einige der Produkte wurden aktualisiert',
            'link-missing' => ''
        ],

        'onepage' => [
            'title' => 'Bestellen',
            'information' => 'Informationen',
            'shipping' => 'Versand',
            'payment' => 'Zahlung',
            'complete' => 'Komplett',
            'review' => 'Rezension',
            'billing-address' => 'Rechnungsadresse',
            'sign-in' => 'Anmelden',
            'company-name' => 'Name der Firma',
            'first-name' => 'Vorname',
            'last-name' => 'Nachname',
            'email' => 'E-Mail',
            'address1' => 'Straße',
            'city' => 'Stadt',
            'state' => 'Bundesland',
            'select-state' => 'Wählen Sie eine Region, ein Bundesland oder eine Provinz aus',
            'postcode' => 'Postleitzahl',
            'phone' => 'Telefon',
            'country' => 'Land',
            'order-summary' => 'Bestellübersicht',
            'shipping-address' => 'Lieferanschrift',
            'use_for_shipping' => 'An diese Adresse liefern',
            'continue' => 'Weiter',
            'shipping-method' => 'Versandart wählen',
            'payment-methods' => 'Zahlungsmethode wählen',
            'payment-method' => 'Zahlungsmethode',
            'summary' => 'Bestellübersichty',
            'price' => 'Preis',
            'quantity' => 'Menge',
            'billing-address' => 'Rechnungsadresse',
            'shipping-address' => 'Lieferanschrift',
            'contact' => 'Kontakt',
            'place-order' => 'Bestellung aufgeben',
            'new-address' => 'Neue Adresse hinzufügen',
            'save_as_address' => 'Diese Adresse speichern',
            'apply-coupon' => 'Gutschein einlösen',
            'amt-payable' => 'Bezahlbarer Betrag',
            'got' => 'Erhalten',
            'free' => 'Frei',
            'coupon-used' => 'Gutschein verwendet',
            'applied' => 'Angewandt',
            'back' => 'Zurück',
            'cash-desc' => 'Barzahlung bei Lieferung',
            'money-desc' => 'Geldüberweisung',
            'paypal-desc' => 'Paypal Standard',
            'free-desc' => 'Dies ist ein kostenloser Versand',
            'flat-desc' => 'Dies ist eine Flatrate',
            'password' => 'Passwort',
            'login-exist-message' => 'Sie haben bereits ein Konto bei uns, melden Sie sich an oder fahren Sie als Gast fort.',
            'enter-coupon-code' => 'Gutscheincode eingeben'
        ],

        'total' => [
            'order-summary' => 'Bestellübersicht',
            'sub-total' => 'Artikel',
            'grand-total' => 'Gesamtsumme',
            'delivery-charges' => 'Versandkosten',
            'tax' => 'Umsatzsteuer',
            'discount' => 'Rabatt',
            'price' => 'Preis',
            'disc-amount' => 'Rabattbetrag',
            'new-grand-total' => 'Neue Gesamtsumme',
            'coupon' => 'Gutschein',
            'coupon-applied' => 'Angewandter Gutschein',
            'remove-coupon' => 'Gutschein entfernen',
            'cannot-apply-coupon' => 'Gutschein kann nicht angewendet werden',
            'invalid-coupon' => 'Gutscheincode ist ungültig.',
            'success-coupon' => 'Gutscheincode erfolgreich angewendet.',
            'coupon-apply-issue' => 'Gutscheincode kann nicht angewendet werden.'
        ],

        'success' => [
            'title' => 'Bestellung erfolgreich aufgegeben',
            'thanks' => 'Vielen Dank für Ihren Auftrag!',
            'order-id-info' => 'Ihre Bestellnummer lautet #:order_id',
            'info' => 'Wir senden Ihnen Ihre Bestelldaten und Tracking-Informationen per E-Mail'
        ]
    ],

    'mail' => [
        'order' => [
            'subject' => 'Bestätigung der neuen Bestellung',
            'heading' => 'Bestellbestätigung!',
            'dear' => 'Sehr geehrte/r :customer_name',
            'dear-admin' => 'Sehr geehrte/r :admin_name',
            'greeting' => 'Danke für Ihre Bestellung :order_id vom :created_at',
            'greeting-admin' => 'Auftragsnummer :order_id vom :created_at',
            'summary' => 'Zusammenfassung der Bestellung',
            'shipping-address' => 'Lieferanschrift',
            'billing-address' => 'Rechnungsadresse',
            'contact' => 'Kontakt',
            'shipping' => 'Versandart',
            'payment' => 'Zahlungsmethode',
            'price' => 'Preis',
            'quantity' => 'Menge',
            'subtotal' => 'Zwischensumme',
            'shipping-handling' => 'Versand & Bearbeitung',
            'tax' => 'Umsatzsteuer',
            'discount' => 'Rabatt',
            'grand-total' => 'Gesamtsumme',
            'final-summary' => 'Vielen Dank für Ihr Interesse an unserem Shop. Nach dem Versand senden wir Ihnen die Sendungsverfolgungsnummer',
            'help' => 'Wenn Sie Hilfe benötigen, kontaktieren Sie uns bitte unter :support_email',
            'thanks' => 'Vielen Dank!',
            'cancel' => [
                'subject' => 'Bestätigung der Bestellungsstornierung',
                'heading' => 'Bestellung storniert',
                'dear' => 'Sehr geehrte/r :customer_name',
                'greeting' => 'Ihre Bestellung mit der Bestellnummer #:order_id vom :created_at wurde storniert',
                'summary' => 'Zusammenfassung der Bestellung',
                'shipping-address' => 'Lieferanschrift',
                'billing-address' => 'Rechnungsadresse',
                'contact' => 'Kontakt',
                'shipping' => 'Versandart',
                'payment' => 'Zahlungsmethode',
                'subtotal' => 'Zwischensumme',
                'shipping-handling' => 'Versand & Bearbeitung',
                'tax' => 'Umsatzsteuer',
                'discount' => 'Rabatt',
                'grand-total' => 'Gesamtsumme',
                'final-summary' => 'Vielen Dank für Ihr Interesse an unserem Shop',
                'help' => 'Wenn Sie Hilfe benötigen, kontaktieren Sie uns bitte unter :support_email',
                'thanks' => 'Vielen Dank!',
            ]
        ],

        'invoice' => [
            'heading' => 'Ihre Rechnung #:invoice_id für die Bestellung #:order_id',
            'subject' => 'Rechnung für Ihre Bestellung #:order_id',
            'summary' => 'Zusammenfassung der Rechnung',
        ],

        'shipment' => [
            'heading' => 'Sendung #:shipment_id wurde für die Bestellung #:order_id generiert',
            'inventory-heading' => 'Neue Sendung #:shipment_id wurde für die Bestellung #:order_id generiert',
            'subject' => 'Versand für Ihre Bestellung #:order_id',
            'inventory-subject' => 'Für die Bestellung #:order_id wurde eine neue Sendung generiert',
            'summary' => 'Zusammenfassung der Sendung',
            'carrier' => 'Zulieferer',
            'tracking-number' => 'Sendungsnummer',
            'greeting' => 'Eine Bestellung :order_id wurde aufgelegt am :created_at',
        ],

        'refund' => [
            'heading' => 'Ihre Rückerstattung #:refund_id für die Bestellung #:order_id',
            'subject' => 'Rückerstattung für Ihre Bestellung #:order_id',
            'summary' => 'Zusammenfassung der Rückerstattung',
            'adjustment-refund' => 'Anpassungsrückerstattung',
            'adjustment-fee' => 'Anpassungsgebühr'
        ],

        'forget-password' => [
            'subject' => 'Kundenpasswort zurücksetzen',
            'dear' => 'Sehr geehrte/r :name',
            'info' => 'Sie erhalten diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto erhalten haben',
            'reset-password' => 'Passwort zurücksetzen',
            'final-summary' => 'Wenn Sie kein Zurücksetzen des Kennworts angefordert haben, sind keine weiteren Maßnahmen erforderlich',
            'thanks' => 'Vielen Dank!'
        ],

        'customer' => [
            'new' => [
                'dear' => 'Sehr geehrte/r :customer_name',
                'username-email' => 'Nutzername/E-Mail',
                'subject' => 'Neukundenregistrierung',
                'password' => 'Passwort',
                'summary' => 'Ihr Konto wurde erstellt.
                Ihre Kontodaten weiter unten: ',
                'thanks' => 'Vielen Dank!',
            ],

            'registration' => [
                'subject' => 'Neukundenregistrierung',
                'customer-registration' => 'Kunde erfolgreich registriert',
                'dear' => 'Sehr geehrte/r :customer_name',
                'greeting' => 'Willkommen und vielen Dank, dass Sie sich bei uns registriert haben!',
                'summary' => 'Ihr Konto wurde nun erfolgreich erstellt und Sie können sich mit Ihrer E-Mail-Adresse und Ihrem Passwort anmelden. Nach dem Anmelden können Sie auf andere Dienste zugreifen, einschließlich der Überprüfung früherer Bestellungen, Wunschliste und der Bearbeitung Ihrer Kontoinformationen.',
                'thanks' => 'Vielen Dank!',
            ],

            'verification' => [
                'heading' => config('app.name') . ' - E-Mail-Verifizierung',
                'subject' => 'Bestätigungsmail',
                'verify' => 'Bestätigen Sie Ihr Konto',
                'summary' => 'Dies ist die E-Mail, um zu überprüfen, ob die von Ihnen eingegebene E-Mail-Adresse Ihre ist.
                Klicken Sie unten auf die Schaltfläche "Konto bestätigen", um Ihr Konto zu bestätigen.'
            ],

            'subscription' => [
                'subject' => 'Abonnement-E-Mail',
                'greeting' => ' Willkommen zu ' . config('app.name') . ' - E-Mail-Abonnement',
                'unsubscribe' => 'Abmelden',
                'summary' => 'Es ist eine Weile her, seit Sie ' . config('app.name') . ' gelesen haben und wir möchten Ihren Posteingang nicht überfluten. Wenn Sie nicht die neuesten
                E-Mail-Marketing-Nachrichten erhalten möchten, klicken Sie auf die Schaltfläche unten.'
            ]
        ]
    ],

    'webkul' => [
        'copy-right' => '© Copyright :year Webkul Software, All rights reserved',
    ],

    'response' => [
        'create-success' => ':name erfolgreich erstellt.',
        'update-success' => ':name erfolgreich aktualisiert.',
        'delete-success' => ':name erfolgreich gelöscht.',
        'submit-success' => ':name erfolgreich eingereicht.'
    ],
];
