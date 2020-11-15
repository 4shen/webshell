<?php

return [
    'invalid_vat_format' => 'La partita IVA indicata ha un formato non corretto',
    'security-warning' => 'Identificata attività sospetta!!!',
    'nothing-to-delete' => 'Niente da eliminare',

    'layouts' => [
        'my-account' => 'Il Mio Account',
        'profile' => 'Profilo',
        'address' => 'Indirizzo',
        'reviews' => 'Recensioni',
        'wishlist' => 'Preferiti',
        'orders' => 'Ordini',
        'downloadable-products' => 'Prodotti Scaricabili'
    ],

    'common' => [
        'error' => 'Qualcosa è andato storto, per favore prova ancora più tardi.',
        'no-result-found' => 'Non abbiamo trovato risultati.'
    ],

    'home' => [
        'page-title' => config('app.name') . ' - Home',
        'featured-products' => 'Prodotti in evidenza',
        'new-products' => 'Nuovi Prodotti',
        'verify-email' => 'Verifica il tuo indirizzo email',
        'resend-verify-email' => 'Reinvia email di verifica'
    ],

    'header' => [
        'title' => 'Account',
        'dropdown-text' => 'Gestione Carrello, Ordini e Preferiti',
        'sign-in' => 'Login',
        'sign-up' => 'Registrati',
        'account' => 'Account',
        'cart' => 'Carrello',
        'profile' => 'Profilo',
        'wishlist' => 'Preferiti',
        'cart' => 'Carrello',
        'logout' => 'Logout',
        'search-text' => 'Cerca prodotti qui'
    ],

    'minicart' => [
        'view-cart' => 'Mostra Carrello',
        'checkout' => 'Cassa',
        'cart' => 'Carrello',
        'zero' => '0'
    ],

    'footer' => [
        'subscribe-newsletter' => 'Iscriviti alla Newsletter',
        'subscribe' => 'Iscriviti',
        'locale' => 'Lingua',
        'currency' => 'Valuta',
    ],

    'subscription' => [
        'unsubscribe' => 'Cancellati',
        'subscribe' => 'Iscriviti',
        'subscribed' => 'Ora sei iscritto al nostro servizio di notifica.',
        'not-subscribed' => 'Non è stato possibile iscriverti al nostro servizio di notifica, prova di nuovo più tardi.',
        'already' => 'Sei già iscritto al nostro servizio di notifica.',
        'unsubscribed' => 'Sei stato rimosso dal nostro servizio di notifica.',
        'already-unsub' => 'Sei già stato cancellato.',
        'not-subscribed' => 'Errore! L\'email non può essere inviata in questo momento, per favore riprovare più tardi.'
    ],

    'search' => [
        'no-results' => 'Nessun risultato trovato',
        'page-title' => config('app.name') . ' - Cerca',
        'found-results' => 'Risultati trovati',
        'found-result' => 'Risultato trovato',
        'analysed-keywords' => 'Analysed Keywords'
    ],

    'reviews' => [
        'title' => 'Titolo',
        'add-review-page-title' => 'Aggiungi Recensione',
        'write-review' => 'Scrivi una recensione',
        'review-title' => 'Dai un titolo alla tua recensione',
        'product-review-page-title' => 'Recensione Prodotto',
        'rating-reviews' => 'Valutazioni e recensioni',
        'submit' => 'INVIA',
        'delete-all' => 'Tutte le recensioni sono state eliminate con successo',
        'ratingreviews' => ':rating Valutazioni e :review Recensioni',
        'star' => 'Stella',
        'percentage' => ':percentage %',
        'id-star' => 'stella',
        'name' => 'Nome',
    ],

    'customer' => [
        'signup-text' => [
            'account_exists' => 'Sei già registrato?',
            'title' => 'Login'
        ],

        'signup-form' => [
            'page-title' => 'Crea subito il tuo profilo',
            'title' => 'Registrati',
            'firstname' => 'Nome',
            'lastname' => 'Cognome',
            'email' => 'Email',
            'password' => 'Password',
            'confirm_pass' => 'Conferma Password',
            'button_title' => 'Registrati',
            'agree' => 'Acconsento',
            'terms' => 'Termini',
            'conditions' => 'Condizioni',
            'using' => 'utilizzando questo sito',
            'agreement' => 'Accordo',
            'success' => 'Account creato con successo.',
            'success-verify' => 'Account creato con successo, una e-mail è stata inviata per verifica.',
            'success-verify-email-unsent' => 'Account creato con successo, ma non è stato possibile inviare l\'email di verifica.',
            'failed' => 'Errore! Non è stato possibile creare il tuo profilo, prova di nuovo più tardi.',
            'already-verified' => 'Il tuo profilo è già stato verificato oppure il link di verifica è scaduto. Prova a chidere una nuova email di verifica.',
            'verification-not-sent' => 'Errore! Problema nell\'invio dell\'email di verifica, prova di nuovo più tardi.',
            'verification-sent' => 'Email di verifica inviata',
            'verified' => 'Il tuo account è stato verificato, prova ora ad autenticarti.',
            'verify-failed' => 'Non possiamo verificare la tua email',
            'dont-have-account' => 'Non risulti registrato sul nostro sito.',
            'customer-registration' => 'CIl cliente è stato registrato con successo'
        ],

        'login-text' => [
            'no_account' => 'Primo accesso?',
            'title' => 'Registrati',
        ],

        'login-form' => [
            'page-title' => 'Login',
            'title' => 'Login',
            'email' => 'Email',
            'password' => 'Password',
            'forgot_pass' => 'Dimenticato Password?',
            'button_title' => 'Login',
            'remember' => 'Ricordami',
            'footer' => '© Copyright :year Webkul Software, Tutti i diritti riservati',
            'invalid-creds' => 'Per favore verifica le tue credenziali e prova di nuovo.',
            'verify-first' => 'Verifica prima il tuo account email.',
            'not-activated' => 'La tua attivazione richiede l\'approvazione di un amministratore',
            'resend-verification' => 'Reinvia l\'email di verifica'
        ],

        'forgot-password' => [
            'title' => 'Recupera Password',
            'email' => 'Email',
            'submit' => 'Richiedi nuova Password',
            'page_title' => 'Hai dimenticato la Password?'
        ],

        'reset-password' => [
            'title' => 'Crea nuova Password',
            'email' => 'Email registrata',
            'password' => 'Password',
            'confirm-password' => 'Conferma Password',
            'back-link-title' => 'Ritorna a Login',
            'submit-btn-title' => 'Aggiorna Password'
        ],

        'account' => [
            'dashboard' => 'Modifica Profilo',
            'menu' => 'Menu',

            'profile' => [
                'index' => [
                    'page-title' => 'Profilo',
                    'title' => 'Profilo',
                    'edit' => 'Modifica',
                ],

                'edit-success' => 'Profilo aggiornato con successo.',
                'edit-fail' => 'Errore! Non è stato possibile aggiornare il profilo, prova nuovamente più tardi.',
                'unmatch' => 'La vecchia password non coincide.',

                'fname' => 'Nome',
                'lname' => 'Cognome',
                'gender' => 'Sesso',
                'other' => 'Altro',
                'male' => 'Uomo',
                'female' => 'Donna',
                'dob' => 'Data di nascita',
                'phone' => 'Telefono',
                'email' => 'Email',
                'opassword' => 'Vecchia Password',
                'password' => 'Password',
                'cpassword' => 'Conferma Password',
                'submit' => 'Aggiorna Profilo',

                'edit-profile' => [
                    'title' => 'Modifica Profilo',
                    'page-title' => 'Modifica Profilo'
                ]
            ],

            'address' => [
                'index' => [
                    'page-title' => 'Indirizzo',
                    'title' => 'Indirizzo',
                    'add' => 'Aggiungi Indirizzo',
                    'edit' => 'Modifica',
                    'empty' => 'Non hai ancora salvato i tuoi indirizzi, prova ad aggiungerne uno cliccando il link qui sotto',
                    'create' => 'Crea Indirizzo',
                    'delete' => 'Elimina',
                    'make-default' => 'Rendi Predefinito',
                    'default' => 'Predefinito',
                    'contact' => 'Contatto',
                    'confirm-delete' =>  'Vuoi veramente eliminare questo indirizzo?',
                    'default-delete' => 'L\'indirizzo predefinito non può essere modificato.',
                    'enter-password' => 'Inserisci la tua Password.',
                ],

                'create' => [
                    'page-title' => 'Aggiungi Indirizzo',
                    'company_name' => 'Ragione Sociale',
                    'first_name' => 'Nome',
                    'last_name' => 'Cognome',
                    'vat_id' => 'Partita IVA',
                    'vat_help_note' => '[Nota: Utilizza il codice paese con la Partita IVA. Es. IT01234567891]',
                    'title' => 'Aggiungi Indirizzo',
                    'street-address' => 'Indirizzo',
                    'country' => 'Paese',
                    'state' => 'Provincia',
                    'select-state' => 'Seleziona provincia',
                    'city' => 'Città',
                    'postcode' => 'CAP',
                    'phone' => 'Telefono',
                    'submit' => 'Salva Indirizzo',
                    'success' => 'Indirizzo aggiunto con successo.',
                    'error' => 'Non è stato possibile aggiungere l\'indirizzo.'
                ],

                'edit' => [
                    'page-title' => 'Modifica Indirizzo',
                    'company_name' => 'Ragione Sociale',
                    'first_name' => 'Nome',
                    'last_name' => 'Cognome',
                    'vat_id' => 'Partita IVA',
                    'title' => 'Modifica Indirizzo',
                    'street-address' => 'Indirizzo',
                    'submit' => 'Salva Indirizzo',
                    'success' => 'Indirizzo aggiornato con successo.',
                ],
                'delete' => [
                    'success' => 'Indirizzo eliminato con successo',
                    'failure' => 'L\'indirizzo non può essere eliminato',
                    'wrong-password' => 'Password errata !'
                ]
            ],

            'order' => [
                'index' => [
                    'page-title' => 'Ordini',
                    'title' => 'Ordini',
                    'order_id' => 'Ordine Nro',
                    'date' => 'Data',
                    'status' => 'Status',
                    'total' => 'Totale',
                    'order_number' => 'Numero Ordine',
                    'processing' => 'In lavorazione',
                    'completed' => 'Completato',
                    'canceled' => 'Cancellato',
                    'closed' => 'Chiuso',
                    'pending' => 'In attesa',
                    'pending-payment' => 'In attesa pagamento',
                    'fraud' => 'Frode'
                ],

                'view' => [
                    'page-tile' => 'Ordine #:order_id',
                    'info' => 'Informazioni',
                    'placed-on' => 'Data Ordine',
                    'products-ordered' => 'Prodotti Ordinati',
                    'invoices' => 'Fatture',
                    'shipments' => 'Spedizioni',
                    'SKU' => 'SKU',
                    'product-name' => 'Articolo',
                    'qty' => 'Qtà',
                    'item-status' => 'Stato Articolo',
                    'item-ordered' => 'Ordinato (:qty_ordered)',
                    'item-invoice' => 'Fatturato (:qty_invoiced)',
                    'item-shipped' => 'spedito (:qty_shipped)',
                    'item-canceled' => 'Cancellato (:qty_canceled)',
                    'item-refunded' => 'Rimborsato (:qty_refunded)',
                    'price' => 'Prezzo',
                    'total' => 'Totale',
                    'subtotal' => 'Subtotale',
                    'shipping-handling' => 'Spedizione',
                    'tax' => 'IVA',
                    'discount' => 'Sconto',
                    'tax-percent' => 'IVA %',
                    'tax-amount' => 'IVA',
                    'discount-amount' => 'Sconto',
                    'grand-total' => 'Totale',
                    'total-paid' => 'Totale Pagato',
                    'total-refunded' => 'Total Rimborsato',
                    'total-due' => 'Totale da pagare',
                    'shipping-address' => 'Indirizzo Spedizione',
                    'billing-address' => 'Indirizzo Ordinante',
                    'shipping-method' => 'Metodo Spedizione',
                    'payment-method' => 'Metodo Pagamento',
                    'individual-invoice' => 'Fattura #:invoice_id',
                    'individual-shipment' => 'Spedizione #:shipment_id',
                    'print' => 'Stampa',
                    'invoice-id' => 'Fattura Nro',
                    'order-id' => 'Ordine Nro',
                    'order-date' => 'Ordine Data',
                    'bill-to' => 'Fatturato a',
                    'ship-to' => 'Spedito a',
                    'contact' => 'Contatto',
                    'refunds' => 'Rimborsi',
                    'individual-refund' => 'Rimborso #:refund_id',
                    'adjustment-refund' => 'Rimborso',
                    'adjustment-fee' => 'Commissione di rimborso',
                    'cancel-btn-title' => 'Cancella',
                    'tracking-number' => 'Tracking Number',
                    'cancel-confirm-msg' => 'Sei sicuro di voler annullare questo ordine ?'
                ]
            ],

            'wishlist' => [
                'page-title' => 'Preferiti',
                'title' => 'Preferiti',
                'deleteall' => 'Elimina tutti',
                'moveall' => 'Aggiungi tutti i Prodotti al Carrello',
                'move-to-cart' => 'Aggiungi al Carrello',
                'error' => 'Non è possibile aggiungere il prodotto ai preferiti per un problema sconosciuto, provare nuovamente più tardi',
                'add' => 'Il prodotto è stato aggiunto ai preferiti',
                'remove' => 'Articolo rimosso dai preferiti',
                'moved' => 'Articolo aggiunto al carrello',
                'option-missing' => 'Le opzioni del prodotto mancano, per questo il prodotto non può essere aggiunto ai preferiti.',
                'move-error' => 'Il prodotto non può essere aggiunto ai preferiti, prova nuovamente più tardi',
                'success' => 'Il prodotto è stato aggiunto ai preferiti',
                'failure' => 'Il prodotto non può essere aggiunto ai preferiti, prova nuovamente più tardi',
                'already' => 'Il prodotto è già presente nei tuoi preferiti',
                'removed' => 'Il prodotto è stato rimosso dai preferiti',
                'remove-fail' => 'Il prodotto non può essere rimosso dai preferiti, prova nuovamente più tardi',
                'empty' => 'Non hai ancora aggiunto prodotti ai tuoi preferiti',
                'remove-all-success' => 'Tutti gli articoli sono stati rimossi dai tuoi preferiti',
            ],

            'downloadable_products' => [
                'title' => 'Prodotti scaricabili',
                'order-id' => 'Id Ordine',
                'date' => 'Data',
                'name' => 'Titolo',
                'status' => 'Status',
                'pending' => 'In attesa',
                'available' => 'Disponibile',
                'expired' => 'Scaduto',
                'remaining-downloads' => 'Download rimasti',
                'unlimited' => 'Illimitati',
                'download-error' => 'Il link per il Download è scaduto.'
            ],

            'review' => [
                'index' => [
                    'title' => 'Recensioni',
                    'page-title' => 'Recensioni'
                ],

                'view' => [
                    'page-tile' => 'Recensione #:id',
                ]
            ]
        ]
    ],

    'products' => [
        'layered-nav-title' => 'Acquista per',
        'price-label' => 'A partire da',
        'remove-filter-link-title' => 'Rimuovi filtri',
        'sort-by' => 'Ordina per',
        'from-a-z' => 'Da A-Z',
        'from-z-a' => 'Da Z-A',
        'newest-first' => 'I più recenti prima',
        'oldest-first' => 'I più datati prima',
        'cheapest-first' => 'Prezzo più basso prima',
        'expensive-first' => 'Prezzo più alto prima',
        'show' => 'Mostra',
        'pager-info' => 'Stai vedendo :showing di :total Items',
        'description' => 'Descrizione',
        'specification' => 'Specifiche',
        'total-reviews' => ':total Recensioni',
        'total-rating' => ':total_rating valutazioni e :total_reviews recensioni',
        'by' => 'Per :name',
        'up-sell-title' => 'Abbiamo trovato altri prodotti che potrebbero piacerti!',
        'related-product-title' => 'Prodotti correlati',
        'cross-sell-title' => 'Altre scelte',
        'reviews-title' => 'Valutazioni e Recensioni',
        'write-review-btn' => 'Scrivi una recensione',
        'choose-option' => 'Scegli una opzione',
        'sale' => 'Promo',
        'new' => 'Nuovo',
        'empty' => 'Nessun prodotto disponibile in questa categoria',
        'add-to-cart' => 'Aggiungi al Carrello',
        'book-now' => 'Prenota ora',
        'buy-now' => 'Compra ora',
        'whoops' => 'Whoops!',
        'quantity' => 'Quantità',
        'in-stock' => 'Disponibile',
        'out-of-stock' => 'Esaurito',
        'view-all' => 'Mostra Tutto',
        'select-above-options' => 'Per favore seleziona prima le opzioni sopra.',
        'less-quantity' => 'La quantità non può essere inferiore a uno.',
        'samples' => 'Campioni',
        'links' => 'Links',
        'sample' => 'Campione',
        'name' => 'Nome',
        'qty' => 'Qtà',
        'starting-at' => 'A partire da',
        'customize-options' => 'Customizza opzioni',
        'choose-selection' => 'Scegli una selezione',
        'your-customization' => 'La tua Personalizzazione',
        'total-amount' => 'Totale',
        'none' => 'Nessuno',
        'available-for-order' => 'Disponibile per lordine',
        'settings' => 'Settings',
        'compare_options' => 'Compare Options',
    ],

    // 'reviews' => [
    //     'empty' => 'Non hai ancora recensito alcun prodotto'
    // ]

    'buynow' => [
        'no-options' => 'Per favore seleziona le opzioni per acquistare questo prodotto.'
    ],

    'checkout' => [
        'cart' => [
            'integrity' => [
                'missing_fields' => 'Mancano alcuni campi obbligatori per questo prodotto.',
                'missing_options' => 'Mancano alcune Opzioni obbligatorie per questo prodotto.',
                'missing_links' => 'I link per il download di questo prodotto sono mancanti.',
                'qty_missing' => 'Almeno un prodotto dovrebbe avere una quantità superiore a 1.',
                'qty_impossible' => 'Non è possibile aggiungere più di un pezzo di questo articolo nel carrello.'
            ],
            'create-error' => 'Si è verificato un problema durante la visualizzazione del carrello.',
            'title' => 'Carrello',
            'empty' => 'Il tuo carrello è ancora vuoto',
            'update-cart' => 'Aggiorna Carrello',
            'continue-shopping' => 'Continua con i tuoi acquisti',
            'proceed-to-checkout' => 'Procedi alla Cassa',
            'remove' => 'Rimuovi',
            'remove-link' => 'Rimuovi',
            'move-to-wishlist' => 'Sposta nella Wishlist',
            'move-to-wishlist-success' => 'Articolo spostato nella tua wishlist.',
            'move-to-wishlist-error' => 'Non è possibile spostare l\'articolo nella tua wishlist, prova ancora.',
            'add-config-warning' => 'Seleziona una opzione prima di aggiungere al carrello.',
            'quantity' => [
                'quantity' => 'Quantità',
                'success' => 'Articoli nel carrello aggiornati con successo.',
                'illegal' => 'La quantità non può essere inferiore a 0.',
                'inventory_warning' => 'La quantità richiesta non è disponibile, prova ancora.',
                'error' => 'Non è posibile aggiornare gli articoli al momento, prova ancora.'
            ],

            'item' => [
                'error_remove' => 'Nessun prodotto da rimuovere nel carrello.',
                'success' => 'Prodotto aggiunto al carrello.',
                'success-remove' => 'Prodotto rimosso dal carrello.',
                'error-add' => 'Il prodotto non può essere aggiunto al carrello, prova ancora.',
            ],
            'quantity-error' => 'La quantità richiesta non è disponibile.',
            'cart-subtotal' => 'Subtotale Carrello',
            'cart-remove-action' => 'Vuoi veramente farlo ?',
            'partial-cart-update' => 'Solo alcuni dei prodotti sono stati aggiornati',
            'link-missing' => '',
            'event' => [
                'expired' => 'Questo evento è terminato.'
            ]
        ],

        'onepage' => [
            'title' => 'Cassa',
            'information' => 'Informazioni',
            'shipping' => 'Spedizione',
            'payment' => 'Pagamento',
            'complete' => 'Completo',
            'review' => 'Revisione',
            'billing-address' => 'Indirizzo Fatturazione',
            'sign-in' => 'Login',
            'company-name' => 'Azienda',
            'first-name' => 'Nome',
            'last-name' => 'Cognome',
            'email' => 'Email',
            'address1' => 'Indirizzo',
            'city' => 'Città',
            'state' => 'Provincia',
            'select-state' => 'Seleziona una provincia',
            'postcode' => 'CAP',
            'phone' => 'Telefono',
            'country' => 'Paese',
            'order-summary' => 'Riepilogo Ordine',
            'shipping-address' => 'Indirizzo Spedizione',
            'use_for_shipping' => 'Spedisci a questo indirizzo',
            'continue' => 'Continua',
            'shipping-method' => 'Seleziona Metodo di Spedizione',
            'payment-methods' => 'Seleziona Metodo di Pagamento',
            'payment-method' => 'Metodo di Pagamento',
            'summary' => 'Riepilogo Ordine',
            'price' => 'Prezzo',
            'quantity' => 'Quantità',
            'billing-address' => 'Indirizzo Fatturazione',
            'shipping-address' => 'Indirizzo Spedizione',
            'contact' => 'Contatto',
            'place-order' => 'Procedi con Ordine',
            'new-address' => 'Aggiungi Nuovo Indirizzo',
            'save_as_address' => 'Salva questo indirizzo',
            'apply-coupon' => 'Codice Promo',
            'amt-payable' => 'Totale da Pagare',
            'got' => 'Got',
            'free' => 'Gratis',
            'coupon-used' => 'Codice Utilizzato',
            'applied' => 'Applicato',
            'back' => 'Indietro',
            'cash-desc' => 'Contrassegno',
            'money-desc' => 'Bonifico',
            'paypal-desc' => 'Paypal',
            'free-desc' => 'Questa è una spedizine gratuita',
            'flat-desc' => 'Questa è una spedizione a prezzo fisso',
            'password' => 'Password',
            'login-exist-message' => 'Sei già registrato nel nostro store, effettua la login o continua come ospite.',
            'enter-coupon-code' => 'Inserisci Codice Promo'
        ],

        'total' => [
            'order-summary' => 'Riepilogo Ordine',
            'sub-total' => 'Articoli',
            'grand-total' => 'Totale',
            'delivery-charges' => 'Spedizione',
            'tax' => 'IVA',
            'discount' => 'Sconto',
            'price' => 'prezzo',
            'disc-amount' => 'Totale Scontato',
            'new-grand-total' => 'Nuovo Totale',
            'coupon' => 'Codice Promo',
            'coupon-applied' => 'Codice Promo Applicato',
            'remove-coupon' => 'Rimuovi Codice Promo',
            'cannot-apply-coupon' => 'Non è possibile Applicare il Codice Promo',
            'invalid-coupon' => 'Il Codice Promo non è valido.',
            'success-coupon' => 'Codice Promo applicato con successo.',
            'coupon-apply-issue' => 'Il Codice Promo non può essere applicato.'
        ],

        'success' => [
            'title' => 'Ordine completato con successo',
            'thanks' => 'Grazie per il tuo ordine!',
            'order-id-info' => 'Il tuo id ordine è #:order_id',
            'info' => 'Ti invieremo via email i dettagli del tuo ordine e le informazioni di tracking'
        ]
    ],

    'mail' => [
        'order' => [
            'subject' => 'Nuova Conferma Ordine',
            'heading' => 'Conferma Ordine!',
            'dear' => ':customer_name',
            'dear-admin' => ':admin_name',
            'greeting' => 'Grazie per il tuo Oridne :order_id su :created_at',
            'greeting-admin' => 'Id Ordine :order_id su :created_at',
            'summary' => 'Riepilogo Ordine',
            'shipping-address' => 'Indirizzo Spedizione',
            'billing-address' => 'Indirizzo Fatturazione',
            'contact' => 'Contatto',
            'shipping' => 'Metodo di Spedizione',
            'payment' => 'Metodo di Pagamento',
            'price' => 'Prezzo',
            'quantity' => 'Quantità',
            'subtotal' => 'Subtotale',
            'shipping-handling' => 'Spedizione',
            'tax' => 'IVA',
            'discount' => 'Sconto',
            'grand-total' => 'Totale',
            'final-summary' => 'Grazie per il tuo interesse nel nostro store, ti invieremo un codice di tracking una volta che la spedizione sarà completata',
            'help' => 'Se hai bisogno di aiuto contattaci qui :support_email',
            'thanks' => 'Grazie!',

            'comment' => [
                'subject' => 'Nuovo commento aggiunto al tuo ordine',
                'dear' => ':customer_name',
                'final-summary' => 'Grazie per aver mostrato interesse per il nostro store',
                'help' => 'Se hai bisogno di aiuto contattaci all\'indirizzo :support_email',
                'thanks' => 'Graze!',
            ],

            'cancel' => [
                'subject' => 'Conferma Cancellazione Ordine',
                'heading' => 'Ordine Cancellato',
                'dear' => ':customer_name',
                'greeting' => 'Il tuo Ordine #:order_id su :created_at è stato cancellato',
                'summary' => 'Riepilogo Ordine',
                'shipping-address' => 'Indirizzo di Spedizione',
                'billing-address' => 'Indirizzo di Fattuazione',
                'contact' => 'Contatti',
                'shipping' => 'Metodo di Spedizione',
                'payment' => 'Metodo di Pagamento',
                'subtotal' => 'Subtotale',
                'shipping-handling' => 'Spedizione',
                'tax' => 'IVA',
                'discount' => 'Sconto',
                'grand-total' => 'Totale',
                'final-summary' => 'Grazie per l\'interesse mostrato nel nostro store',
                'help' => 'Se hai bisogno di qualsiasi tipo di aiuto contattaci a :support_email',
                'thanks' => 'Grazie!',
            ]
        ],

        'invoice' => [
            'heading' => 'Fattura #:invoice_id per l\'Ordine #:order_id',
            'subject' => 'Fattura per ordine #:order_id',
            'summary' => 'Dettaglio Fattura',
        ],

        'shipment' => [
            'heading' => 'La Spedizione #:shipment_id relativa all\'Ordine #:order_id è stata generata ',
            'inventory-heading' => 'Nuova Spedizione #:shipment_id relativa all\'Ordine #:order_id è stata generata',
            'subject' => 'Spedizione per il tuo ordine #:order_id',
            'inventory-subject' => 'Nuova spedizione generata per l\'Ordine #:order_id',
            'summary' => 'Riepilogo Spedizione',
            'carrier' => 'Corriere',
            'tracking-number' => 'Codice Tracking',
            'greeting' => 'Un ordine :order_id è stato piazzato su :created_at',
        ],

        'refund' => [
            'heading' => 'Il tuo rimborso #:refund_id per l\'Ordine #:order_id',
            'subject' => 'Rimborso per il tuo ordine #:order_id',
            'summary' => 'Riepilogo rimborso',
            'adjustment-refund' => 'Rimborso accordato',
            'adjustment-fee' => 'Commissione di rimborso'
        ],

        'forget-password' => [
            'subject' => 'Generazione Nuova Password',
            'dear' => ':name',
            'info' => 'Ricevi questa email perchè abbiamo ricevuto una richiesta di generazione di nuova password per il tuo account',
            'reset-password' => 'Generazione nuova Password',
            'final-summary' => 'Se non hai inviato tu questa richiesta, non è necessario effettuare alcuna operazione',
            'thanks' => 'Grazie!'
        ],

        'customer' => [
            'new' => [
                'dear' => 'Gentile :customer_name',
                'username-email' => 'UserName/Email',
                'subject' => 'Nuova registrazione cliente',
                'password' => 'Password',
                'summary' => 'Il tuo account è stato creato.
                I dettagli del tuo account sono i seguenti: ',
                'thanks' => 'Grazie!',
            ],

            'registration' => [
                'subject' => 'Nuova registrazione cliente',
                'customer-registration' => 'Cliente registrato con successo',
                'dear' => 'Gentile :customer_name',
                'greeting' => 'Benvenuto e grazie per esserti registrato!',
                'summary' => 'Il tuo account è stato creato e puoi ora effettuare la login utilizzando il tuo indirizzo email e la password che hai scelto. Una volta effettuato l\'accesso, potrai accedere ad altri servizi tra cui revisione ordini passati, gestione prodotti preferiti e modifica dei tuoi dati.',
                'thanks' => 'Grazie!',
            ],

            'verification' => [
                'heading' => config('app.name') . ' - Email di Verifica',
                'subject' => 'Email di verifica',
                'verify' => 'Verifica il tuo Account',
                'summary' => 'Questa email serve a verificare che l\'indirizzo email che hai inserito ti appartenga veramente.
                Clicca il bottone Verifica il tuo Account qui sotto per verificare il tuo account.'
            ],

            'subscription' => [
                'subject' => 'Email Iscrizione',
                'greeting' => ' Benvenuto ' . config('app.name') . ' - Email Iscrizione',
                'unsubscribe' => 'Cancellati',
                'summary' => 'Grazie per avere scelto di ricevere le nostre email. È passato un po\' di tempo da quando hai letto le email di ' . config('app.name') . '. Non è un nostro desidero inondare la tua casella email con le nostre comunicazioni. Se desideri comunque 
                non ricevere più le nostre news clicca il bottone qui sotto.'
            ]
        ]
    ],

    'webkul' => [
        'copy-right' => '© Copyright :year Webkul Software, Tutti i diritti riservati',
    ],

    'response' => [
        'create-success' => ':name creato con successoy.',
        'update-success' => ':name aggiornato con successo.',
        'delete-success' => ':name eliminato con successo.',
        'submit-success' => ':name inviato con successo.'
    ],
];