<?php

return [
    'invalid_vat_format' => 'Podany numer VAT ma niewłaściwy format',
    'security-warning' => 'Wykryto podejrzane działanie!!!',
    'nothing-to-delete' => 'Nic do usunięcia',

    'layouts' => [
        'my-account' => 'Moje konto',
        'profile' => 'Profil',
        'address' => 'Adres',
        'reviews' => 'Opinie',
        'wishlist' => 'Lista wyboru',
        'orders' => 'Zamówienia',
        'downloadable-products' => 'Produkty do pobrania'
    ],

    'common' => [
        'error' => 'Coś poszło nie tak. Proszę spróbować później.',
        'no-result-found' => 'Nie znaleźliśmy żadnych zapisów.'
    ],

    'home' => [
        'page-title' => config('app.name') . ' - Home',
        'featured-products' => 'Polecane produkty',
        'new-products' => 'Nowe Produkty',
        'verify-email' => 'Zweryfikuj swoje konto e-mail',
        'resend-verify-email' => 'Wyślij ponownie e-mail weryfikacyjny'
    ],

    'header' => [
        'title' => 'Konto',
        'dropdown-text' => 'Zarządzaj koszykiem, zamówieniami i listą wyboru',
        'sign-in' => 'Zaloguj się',
        'sign-up' => 'Zapisz się',
        'account' => 'Konto',
        'cart' => 'Koszyk',
        'profile' => 'Profil',
        'wishlist' => 'Lista wyboru',
        'cart' => 'Koszyk',
        'logout' => 'Wyloguj się',
        'search-text' => 'Tutaj wyszukasz produkty'
    ],

    'minicart' => [
        'view-cart' => 'Zobacz Koszyk',
        'checkout' => 'Kontrola',
        'cart' => 'Koszyk',
        'zero' => '0'
    ],

    'footer' => [
        'subscribe-newsletter' => 'Subskrybuj Newsletter',
        'subscribe' => 'Subskrybuj',
        'locale' => ' Ustawienia regionalne',
        'currency' => 'Waluty',
    ],

    'subscription' => [
        'unsubscribe' => 'Anuluj subskrubcję',
        'subscribe' => 'Subskrybuj',
        'subscribed' => 'Jesteś teraz subskrybentem maili subskrypcyjnych.',
        'not-subscribed' => 'Nie możesz być teraz zapisany do subskrypcji e-maili, spróbuj ponownie później.',
        'already' => 'Jesteś już zapisany do naszej listy subskrypcyjnej.',
        'unsubscribed' => 'Zostałeś wypisany z subskrypcji',
        'already-unsub' => 'Jesteś już wypisany.',
        'not-subscribed' => 'Błąd! Mail nie może zostać wysłany obecnie, spróbuj ponownie później..'
    ],

    'search' => [
        'no-results' => 'Nie znaleziono wyników',
        'page-title' => config('app.name') . ' - Szukaj',
        'found-results' => 'Dostępne wyniki wyszukiwania',
        'found-result' => 'Dostępny wynik wyszukiwania',
        'analysed-keywords' => 'Analysed Keywords'
    ],

    'reviews' => [
        'title' => 'Tytuł',
        'add-review-page-title' => 'Dodaj recenzję',
        'write-review' => 'Napisz recenzję',
        'review-title' => 'Nadaj opinii tytuł',
        'product-review-page-title' => 'Opinia o produkcie',
        'rating-reviews' => 'Oceny i recenzje',
        'submit' => 'WYŚLIJ',
        'delete-all' => 'Wszystkie recenzje zostały pomyślnie usunięte',
        'ratingreviews' => ':rating Ocen & :review Opinii',
        'star' => 'Gwiazdka',
        'percentage' => ':procent %',
        'id-star' => 'gwiazdka',
        'name' => 'Nazwa',
    ],

    'customer' => [
        'signup-text' => [
            'account_exists' => 'Posiadasz już konto',
            'title' => 'Zaloguj się'
        ],

        'signup-form' => [
            'page-title' => 'Create New Customer Account',
            'title' => 'Zarejestruj się',
            'firstname' => 'Imię',
            'lastname' => 'Nazwisko',
            'email' => 'Email',
            'password' => 'Hasło',
            'confirm_pass' => 'Potwierdź hasło',
            'button_title' => 'Zarejestruj się',
            'agree' => 'Agree',
            'terms' => 'Warunki',
            'conditions' => 'korzystania',
            'using' => 'by using this website',
            'agreement' => 'Umowa',
            'success' => 'Konto utworzone pomyślnie.',
            'success-verify' => 'Konto zostało utworzone pomyślnie, wysłano wiadomość e-mail w celu weryfikacji.',
            'success-verify-email-unsent' => 'Konto zostało utworzone pomyślnie, lecz e-mail weryfikacyjny nie został wysłany.',
            'failed' => 'Błąd! Nie można utworzyć konta, spróbuj ponownie później.',
            'success-verify-email-unsent' => 'Twoje konto jest już zweryfikowane lub spróbuj ponownie wysłać nowy e-mail weryfikacyjny.',
            'verification-not-sent' => 'Błąd! Problem z wysłaniem e-maila weryfikacyjnego, spróbuj ponownie później.',
            'verification-sent' => 'Wysłano e-mail weryfikacyjny',
            'verified' => 'Twoje konto zostało zweryfikowane, spróbuj się teraz zalogować.',
            'verify-failed' => 'Nie możemy zweryfikować twojego konta pocztowego.',
            'dont-have-account' => 'Nie posiadasz u nas konta.',
            'customer-registration' => 'Klient zarejestrowany pomyślnie'
        ],

        'login-text' => [
            'no_account' => 'Nie posiadasz konta',
            'title' => 'Zapisz się',
        ],

        'login-form' => [
            'page-title' => 'Login klienta',
            'title' => 'Zaloguj się',
            'email' => 'Email',
            'password' => 'Hasło',
            'forgot_pass' => 'Nie pamiętasz hasła?',
            'button_title' => 'Zaloguj się',
            'remember' => '  Zapamiętaj mnie',
            'footer' => '© Copyright :year Webkul Software, wszelkie prawa zastrzeżone',
            'invalid-creds' => 'Sprawdź swoje dane uwierzytelniające i spróbuj ponownie.',
            'verify-first' => 'Najpierw zweryfikuj swoje konto e-mail.',
            'not-activated' => 'Twoja aktywacja wymaga zgody administratora',
            'resend-verification' => 'Wyślij ponownie wiadomość weryfikacyjną'
        ],

        'forgot-password' => [
            'title' => 'Odzyskaj hasło',
            'email' => 'Email',
            'submit' => '    Wyślij hasło resetowania na adres Email',
            'page_title' => 'Nie pamiętasz hasła?'
        ],

        'reset-password' => [
            'title' => 'Resetuj hasło',
            'email' => 'Zarejestrowany email',
            'password' => 'Hasło',
            'confirm-password' => 'Potwierdź hasło',
            'back-link-title' => 'Powrót do logowania',
            'submit-btn-title' => 'Resetuj hasło'
        ],

        'account' => [
            'dashboard' => 'Edytuj profil',
            'menu' => 'Menu',

            'profile' => [
                'index' => [
                    'page-title' => 'Profil',
                    'title' => 'Profil',
                    'edit' => 'Edytuj',
                ],

                'edit-success' => 'Profil zaktualizowany pomyślnie.',
                'edit-fail' => 'Błąd! Nie można zaktualizować profilu, spróbuj ponownie później.',
                'unmatch' => 'Stare hasło nie pasuje.',

                'fname' => 'Imię',
                'lname' => 'Nazwisko',
                'gender' => 'Płeć',
                'other' => 'Other',
                'male' => 'Mężczyzna',
                'female' => 'Kobieta',
                'dob' => 'Data urodzenia',
                'phone' => 'Telefon',
                'email' => 'Email',
                'opassword' => 'Stare hasło',
                'password' => 'Hasło',
                'cpassword' => 'Potwierdź hasło',
                'submit' => 'Zaktualizuj profil',

                'edit-profile' => [
                    'title' => 'Edytuj profil',
                    'page-title' => 'Edytuj dane profilu'
                ]
            ],

            'address' => [
                'index' => [
                    'page-title' => 'Adres',
                    'title' => 'Adres',
                    'add' => 'Dodaj adres',
                    'edit' => 'Edytuj',
                    'empty' => 'Nie masz żadnych zapisanych adresów, spróbuj je utworzyć, klikając poniższy link',
                    'create' => 'Utwórz adres',
                    'delete' => 'Usuń',
                    'make-default' => 'Ustaw jako domyślny',
                    'default' => 'Domyślny',
                    'contact' => 'Kontakt',
                    'confirm-delete' =>  'Czy na pewno chcesz usunąć ten adres?',
                    'default-delete' => 'Nie można zmienić domyślnego adresu .',
                    'enter-password' => 'Wprowadź hasło.',
                ],

                'create' => [
                    'page-title' => 'Dodaj formularz adresu',
                    'company_name' => 'Nazwa firmy',
                    'first_name' => 'Imię',
                    'last_name' => 'Nazwisko',
                    'vat_id' => 'Numer VAT',
                    'vat_help_note' => '[Uwaga: użyj kodu kraju z identyfikatorem VAT. Na przykład. PL01234567891]',
                    'title' => 'Dodaj adres',
                    'street-address' => 'Ulica',
                    'country' => 'Kraj',
                    'state' => 'Stan',
                    'select-state' => 'Wybierz region, stan lub prowincję, województwo',
                    'city' => 'Miasto',
                    'postcode' => 'Kod pocztowy',
                    'phone' => 'Telefon',
                    'submit' => 'Zapisz adres',
                    'success' => 'Adres został pomyślnie dodany.',
                    'error' => 'Nie można dodać adresu.'
                ],

                'edit' => [
                    'page-title' => 'Edytuj adres',
                    'company_name' => 'Nazwa firmy',
                    'first_name' => 'Imię',
                    'last_name' => 'Nazwisko',
                    'vat_id' => 'Numer VAT',
                    'title' => 'Edytuj adres',
                    'street-address' => 'Ulica',
                    'submit' => 'Zapisz adres',
                    'success' => 'Adres został zaktualizowany pomyślnie.',
                ],
                'delete' => [
                    'success' => 'Adres został usunięty pomyślnie.',
                    'failure' => 'Nie można usunąć adresu',
                    'wrong-password' => 'Błędne hasło!'
                ]
            ],

            'order' => [
                'index' => [
                    'page-title' => 'Zamówienia',
                    'title' => 'Zamówienia',
                    'order_id' => 'Identyfikator zamówienia',
                    'date' => 'Data',
                    'status' => 'Status',
                    'total' => 'Ogółem',
                    'order_number' => 'Numer zamówienia',
                    'processing' => 'Przetwarzanie',
                    'completed' => 'ukończone',
                    'canceled' => 'anulowano',
                    'closed' => 'zamknięto',
                    'pending' => 'w toku',
                    'pending-payment' => 'Płatność w toku',
                    'fraud' => 'Oszustwo'
                ],

                'view' => [
                    'page-tile' => 'Zamówienie #:order_id',
                    'info' => 'Information',
                    'placed-on' => 'Umieszczone na',
                    'products-ordered' => 'Zamówione produkty',
                    'invoices' => 'Faktury',
                    'shipments' => 'Przesyłki',
                    'SKU' => 'SKU',
                    'product-name' => 'Nazwa',
                    'qty' => 'Ilość',
                    'item-status' => 'Status przedmiotu',
                    'item-ordered' => 'Zamówił (:qty_ordered)',
                    'item-invoice' => 'Zafakturowano (:qty_invoiced)',
                    'item-shipped' => 'Wysłano (:qty_shipped)',
                    'item-canceled' => 'Anulowano (:qty_canceled)',
                    'item-refunded' => 'Zwrócono (:qty_refunded)',
                    'price' => 'Cena',
                    'total' => 'Ogółem',
                    'subtotal' => 'Suma częściowa',
                    'shipping-handling' => 'Wysyłka i obsługa',
                    'tax' => 'Podatek',
                    'discount' => 'Rabat',
                    'tax-percent' => 'Procent podatku',
                    'tax-amount' => 'Kwota podatku',
                    'discount-amount' => 'Kwota rabatu',
                    'grand-total' => 'Suma łączna',
                    'total-paid' => 'Łącznie zapłacono',
                    'total-refunded' => 'Razem zwrócono',
                    'total-due' => 'Total Due',
                    'shipping-address' => 'Adres wysyłki',
                    'billing-address' => 'Adres rozliczeniowy',
                    'shipping-method' => 'Metoda wysyłki',
                    'payment-method' => 'Metoda płatności',
                    'individual-invoice' => '„Faktura #:invoice_id',
                    'individual-shipment' => 'Przesyłka #:shipment_id',
                    'print' => 'Drukuj',
                    'invoice-id' => 'Identyfikator faktury',
                    'order-id' => 'Identyfikator zamówienia',
                    'order-date' => 'Data zamówienia',
                    'bill-to' => 'Bill to',
                    'ship-to' => 'Dostawa do',
                    'contact' => 'Kontakt',
                    'refunds' => 'Zwroty',
                    'individual-refund' => 'Refundacja #:refund_id',
                    'adjustment-refund' => 'Wyrównania kosztów zwrotu',
                    'adjustment-fee' => 'Opłata za dostosowanie',
                    'cancel-btn-title' => 'Anuluj',
                    'tracking-number' => 'numer przesyłki',
                    'cancel-confirm-msg' => 'Czy na pewno chcesz anulować to zamówienie ?'
                ]
            ],

            'wishlist' => [
                'page-title' => 'Lista wyboru',
                'title' => 'Lista wyboru',
                'deleteall' => 'Usuń wszystko',
                'moveall' => '„Przenieś wszystkie produkty do koszyka',
                'move-to-cart' => 'Przenieś do koszyka',
                'error' => 'Nie można dodać produktu do listy wyboru z powodu nieznanych problemów, sprawdź później',
                'add' => 'Produkt został pomyślnie dodany do listy wyboru',
                'remove' => 'Produkt został pomyślnie usunięty z listy wyboru',
                'moved' => 'Produkt pomyślnie przeniesiono do koszyka',
                'option-missing' => 'Brak opcji produktu, więc produktu nie można dodać na listę wyboru.',
                'move-error' => ' „Nie można dodać produktu do listy wyboru. Spróbuj ponownie później',
                'success' => 'Produkt został pomyślnie dodany do listy wyboru',
                'failure' => 'Nie można dodać produktu do listy wyboru. Spróbuj ponownie później',
                'already' => 'Produkt jest już na Twojej liście',
                'removed' => 'Produkt pomyślnie usunięto z listy wyboru',
                'remove-fail' => 'Nie można usunąć produktu z listy życzeń. Spróbuj ponownie później',
                'empty' => 'Nie masz dodanych żadnych przedmiotów do listy wyboru',
                'remove-all-success' => 'Wszystkie produkty z Twojej listy życzeń zostały usunięte',
            ],

            'downloadable_products' => [
                'title' => 'Produkty do pobrania',
                'order-id' => 'Identyfikator zamówienia',
                'date' => 'Data',
                'name' => 'Tytuł',
                'status' => 'Status',
                'pending' => 'w toku',
                'available' => 'dostępny',
                'expired' => 'wygasł',
                'remaining-downloads' => 'Pozostałe pliki do pobrania',
                'unlimited' => 'Bez limitu',
                'download-error' => 'Link do pobrania wygasł.'
            ],

            'review' => [
                'index' => [
                    'title' => 'Recenzje',
                    'page-title' => 'Recenzje'
                ],

                'view' => [
                    'page-tile' => 'Recenzja #:id',
                ]
            ]
        ]
    ],

    'products' => [
        'layered-nav-title' => 'Kupować przez',
        'price-label' => 'w cenie od',
        'remove-filter-link-title' => 'Wyczyść wszystko',
        'sort-by' => 'Sortuj według',
        'from-a-z' => 'Od A-Z',
        'from-z-a' => 'Od Z-A',
        'newest-first' => 'Od najnowszych',
        'oldest-first' => 'Od najstarszych',
        'cheapest-first' => 'Od najtańszych',
        'expensive-first' => 'Od najdroższych',
        'show' => 'Pokaż',
        'pager-info' => 'Wyświetlanie :showing of :total Items',
        'description' => 'Opis',
        'specification' => 'Specyfikacja',
        'total-reviews' => ':total Recenzje',
        'total-rating' => ':total_rating Oceny & :total_reviews Recenzje',
        'by' => 'Według :name',
        'up-sell-title' => 'Znaleźliśmy inne produkty, które mogą Ci się spodobać!',
        'related-product-title' => 'Powiązane produkty ',
        'cross-sell-title' => 'Więcej opcji',
        'reviews-title' => 'Oceny i recenzje',
        'write-review-btn' => 'Napisz recenzję',
        'choose-option' => 'Wybierz opcję',
        'sale' => 'Wyprzedaż',
        'new' => 'Nowość',
        'empty' => 'Brak produktów w tej kategorii',
        'add-to-cart' => 'Dodaj do koszyka',
        'book-now' => 'Rezerwuj teraz',
        'buy-now' => 'Kup teraz',
        'whoops' => 'Whoops!',
        'quantity' => 'Ilość',
        'in-stock' => 'W magazynie',
        'out-of-stock' => 'brak w magazynie',
        'view-all' => 'Wyświetl wszystko',
        'select-above-options' => 'Najpierw wybierz powyższe opcje.',
        'less-quantity' => 'Ilość nie może być mniejsza niż jeden.',
        'samples' => 'Próbki',
        'links' => 'Linki',
        'sample' => 'Próbka',
        'name' => 'Nazwa',
        'qty' => 'Ilość',
        'starting-at' => 'Począwszy od',
        'customize-options' => 'Dostosuj opcje',
        'choose-selection' => 'Choose a selection',
        'your-customization' => 'Twoja personalizacja',
        'total-amount' => 'Całkowita kwota',
        'none' => 'Żaden',
        'available-for-order' => 'Dostępne na zamówienie',
        'settings' => 'Settings',
        'compare_options' => 'Compare Options',
    ],

    // 'reviews' => [
    //     'empty' => 'Nie masz jeszcze recenzji żadnego produktu'
    // ]

    'buynow' => [
        'no-options' => 'Proszę wybrać opcje przed zakupem tego produktu.'
    ],

    'checkout' => [
        'cart' => [
            'integrity' => [
                'missing_fields' => 'rak niektórych wymaganych pól dla tego produktu.',
                'missing_options' => 'Brak opcji wyboru dla tego produktu.',
                'missing_links' => 'Brak linków do pobrania dla tego produktu.',
                'qty_missing' => 'Przynajmniej jeden produkt powinien zawierać ilośćwiększą niż 1',
                'qty_impossible' => 'Nie można dodać więcej niż jednego z tych produktów do koszyka.'
            ],
            'create-error' => 'Wystąpił problem podczas tworzenia instancji koszyka.',
            'title' => 'Koszyk zakupu',
            'empty' => 'Twój koszyk jest pusty',
            'update-cart' => 'Zaktualizuj koszyk',
            'continue-shopping' => 'Kontynuuj zakupy',
            'proceed-to-checkout' => 'Przejdź do kasy”',
            'remove' => 'Usuń',
            'remove-link' => 'Usuń',
            'move-to-wishlist' => 'Przenieś na listę wyboru',
            'move-to-wishlist-success' => 'Produkt został pomyślnie przeniesiony na listę wyboru.',
            'move-to-wishlist-error' => 'Nie można przenieść ptoduktu na listę życzeń, spróbuj ponownie później.',
            'add-config-warning' => 'Wybierz opcję przed dodaniem do koszyka.',
            'quantity' => [
                'quantity' => 'Ilość',
                'success' => 'Produkty w koszyku zostały pomyślnie zaktualizowane.',
                'illegal' => 'Ilość nie może być mniejsza niż jeden.',
                'inventory_warning' => 'Żądana ilość nie jest dostępna, spróbuj ponownie później.',
                'error' => 'W tej chwili nie można zaktualizować produktów. Spróbuj ponownie później.'
            ],

            'item' => [
                'error_remove' => 'Brak produktów do usunięcia z koszyka.',
                'success' => 'Produkt został pomyślnie dodany do koszyka.',
                'success-remove' => 'Produkt został pomyślnie usunięty z koszyka.',
                'error-add' => 'Nie można dodać produktu do koszyka, spróbuj ponownie później.',
            ],
            'quantity-error' => 'Żądana ilość nie jest dostępna.',
            'cart-subtotal' => 'Suma częściowa koszyka',
            'cart-remove-action' => 'Czy na pewno chcesz to zrobić ?',
            'partial-cart-update' => 'Tylko niektóre produkty zostały zaktualizowane',
            'event' => [
                'expired' => 'To wydarzenie wygasło.'
            ]
        ],

        'onepage' => [
            'title' => 'Kasa',
            'information' => 'Informacje',
            'shipping' => 'Wysyłka',
            'payment' => 'Płatność',
            'complete' => 'Kompletna',
            'review' => 'Przejrzeć',
            'billing-address' => 'Adres rozliczeniowy',
            'sign-in' => 'Zaloguj się',
            'company-name' => 'Nazwa firmy',
            'first-name' => 'Imię',
            'last-name' => 'Nazwisko',
            'email' => 'Email',
            'address1' => 'ulica',
            'city' => 'Miasto',
            'state' => 'Stan',
            'select-state' => 'Wybierz region, stan, prowincję lub województwo',
            'postcode' => 'Kod pocztowy',
            'phone' => 'Telefon',
            'country' => 'Kraj',
            'order-summary' => 'Podsumowanie zamówienia',
            'shipping-address' => 'Adres wysyłki',
            'use_for_shipping' => 'Wyślij na ten adres',
            'continue' => 'Kontynuuj',
            'shipping-method' => 'Wybierz metodę wysyłki',
            'payment-methods' => 'Wybierz metodę płatności',
            'payment-method' => 'Metoda płatności',
            'summary' => 'Podsumowanie zamówienia',
            'price' => 'Cena',
            'quantity' => 'Ilość',
            'billing-address' => 'Adres rozliczeniowy',
            'shipping-address' => 'Adres wysyłki',
            'contact' => 'Kontakt',
            'place-order' => 'Złóż zamówienie',
            'new-address' => 'Dodaj nowy adres',
            'save_as_address' => 'Zapisz ten adres',
            'apply-coupon' => 'Zastosuj kupon',
            'amt-payable' => 'Kwota do zapłaty',
            'got' => 'Dostawa',
            'free' => 'Darmowa',
            'coupon-used' => 'Wykorzystano kupon',
            'applied' => 'Zastosuj',
            'back' => 'Wstecz',
            'cash-desc' => 'Płatność przy odbiorze',
            'money-desc' => 'Przelew pieniężny',
            'paypal-desc' => 'Paypal Standard',
            'free-desc' => 'Z darmową wysyłką',
            'flat-desc' => 'Ze stawką ryczałtową',
            'password' => 'Hasło',
            'login-exist-message' => 'Masz już konto, zaloguj się lub kontynuuj jako gość.',
            'enter-coupon-code' => 'Wprowadź kod kuponu'
        ],

        'total' => [
            'order-summary' => 'Podsumowanie zamówienia',
            'sub-total' => 'Produkty',
            'grand-total' => 'Suma łączna',
            'delivery-charges' => 'Koszty dostawy',
            'tax' => 'Podatek',
            'discount' => 'Rabat',
            'price' => 'Cena',
            'disc-amount' => 'Kwota zdyskontowana',
            'new-grand-total' => 'Nowa suma łączna',
            'coupon' => 'Kupon',
            'coupon-applied' => 'Zastosuj kupon',
            'remove-coupon' => 'Usuń kupon',
            'cannot-apply-coupon' => 'Nie można zastosować kuponu',
            'invalid-coupon' => 'Kod kuponu jest nieprawidłowy.',
            'success-coupon' => 'Kod kuponu został pomyślnie zastosowany.',
            'coupon-apply-issue' => 'Nie można zastosować kodu kuponu.'
        ],

        'success' => [
            'title' => 'Zamówienie zostało złożone pomyślnie',
            'thanks' => 'TDziękujemy za zamówienie!!',
            'order-id-info' => 'Twój identyfikator zamówienia to #:order_id',
            'info' => 'Prześlemy Ci wiadomość e-mail ze szczegółami zamówienia i informacją o śledzeniu'
        ]
    ],

    'mail' => [
        'order' => [
            'subject' => 'Potwierdzenie nowego zamówienia',
            'heading' => 'OPotwierdzenie zamówienia!',
            'dear' => 'Drogi :customer_name',
            'dear-admin' => 'Drogi :admin_name',
            'greeting' => 'Dziękujemy za zamówienie :order_id złożone na :created_at',
            'greeting-admin' => 'Identyfikator zamówienia :order_id umieszczony na :created_at',
            'summary' => 'Podsumowanie zamówienia',
            'shipping-address' => 'Adres wysyłki',
            'billing-address' => 'Adres rozliczeniowy',
            'contact' => 'Kontakt',
            'shipping' => 'Metoda wysyłki',
            'payment' => 'Metoda płatności',
            'price' => 'Cena',
            'quantity' => 'Ilość',
            'subtotal' => 'Suma częściowa',
            'shipping-handling' => 'Wysyłka i obsługa',
            'tax' => 'Podatek',
            'discount' => 'Rabat',
            'grand-total' => 'Suma łączna',
            'final-summary' => 'TDziękujemy za zainteresowanie naszym sklepem, a po podsumowaniu wyślemy ci numer śledzenia',
            'help' => 'Jeśli potrzebujesz jakiejkolwiek pomocy, skontaktuj się z nami pod adresem :support_email',
            'thanks' => 'Dzięki!',
            'cancel' => [
                'subject' => 'Potwierdź anulowanie zamówienia',
                'heading' => 'Zamówienie anulowane',
                'dear' => 'Drogi :customer_name',
                'greeting' => 'Twoje zamówienie o numerze id #:order_id złożonym na :created_at zostało anulowane',
                'summary' => 'Podsumowanie zamówienia',
                'shipping-address' => 'Adres wysyłki',
                'billing-address' => 'Adres rozliczeniowy',
                'contact' => 'Kontakt',
                'shipping' => 'Metoda wysyłki',
                'payment' => 'Metoda płatności',
                'subtotal' => 'Suma częściowa',
                'shipping-handling' => 'Wysyłka i obsługa',
                'tax' => 'Podatek',
                'discount' => 'Rabat',
                'grand-total' => 'Suma łączna',
                'final-summary' => 'Dziękujemy za zakupy w naszym sklepie',
                'help' => 'Jeśli potrzebujesz jakiejkolwiek pomocy, skontaktuj się z nami pod adresem :support_email',
                'thanks' => 'Dzięki!',
            ]
        ],

        'invoice' => [
            'heading' => 'Twój numer faktury #:invoice_id dla numeru zamówienia #:order_id',
            'subject' => 'Faktura za zamówienie nr #:order_id',
            'summary' => 'Podsumowanie faktury',
        ],

        'shipment' => [
            'heading' => 'Numer przesyłki #:shipment_id  has been generated for Order #:order_id',
            'inventory-heading' => 'New shipment #:shipment_id został wygenerowany dla numeru zamówienia #:order_id',
            'subject' => 'Przesyłka dla Twojego zamówienia nr #:order_id',
            'inventory-subject' => 'Wygenerowano nową wysyłkę dla numeru zamówienia #:order_id',
            'summary' => 'Podsumowanie przesyłki',
            'carrier' => 'Przewoźnik',
            'tracking-number' => 'Numer przesyłki',
            'greeting' => 'Zamówienie :order_id zostało złożone na :created_at',
        ],

        'refund' => [
            'heading' => 'Twój numer refundacji #:refund_id dla numeru zamówienia #:order_id',
            'subject' => 'Zwrot pieniędzy za zamówienie nr #:order_id',
            'summary' => 'Podsumowanie zwrotu',
            'adjustment-refund' => 'Zwrot wyrównania',
            'adjustment-fee' => 'Opłata za dostosowanie'
        ],

        'forget-password' => [
            'subject' => 'Resetowanie hasła klienta',
            'dear' => 'Drogi/a :name',
            'info' => 'Otrzymujesz tego e-maila, ponieważ otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta',
            'reset-password' => 'Zresetuj hasło',
            'final-summary' => 'Jeśli nie zażądałeś resetowania hasła, nie musisz podejmować żadnych dalszych działań',
            'thanks' => 'Dzięki!'
        ],

        'customer' => [
            'new' => [
                'dear' => 'Drogi/a :customer_name',
                'username-email' => 'Nazwa użytkownika/e-mail',
                'subject' => 'Rejestracja nowego klienta',
                'password' => 'Hasło',
                'summary' => 'Twoje konto zostało utworzone.
                Szczegóły twojego konta są poniżej: ',
                'thanks' => 'Dzięki!',
            ],

            'registration' => [
                'subject' => 'Rejestracja nowego klienta',
                'customer-registration' => 'Klient pomyślnie zarejestrowany',
                'dear' => 'Drogi/a :customer_name',
                'greeting' => 'Witamy i dziękujemy za rejestrację w naszym sklepie!',
                'summary' => 'Twoje konto zostało pomyślnie utworzone i możesz zalogować się przy użyciu adresu e-mail i wybranego hasła. Po zalogowaniu będziesz mieć dostęp do innych usług, w tym do przeglądania poprzednich zamówień, list wyboru i edycji informacji o koncie.',
                'thanks' => 'Dzięki!',
            ],

            'verification' => [
                'heading' => config('app.name') . ' -  Weryfikacja adresu e-mail',
                'subject' => 'Mail weryfikujący',
                'verify' => 'Zweryfikuj swoje konto',
                'summary' => 'To jest wiadomość sprawdzająca, czy wprowadzony adres e-mail należy do Ciebie.
                Kliknij przycisk Zweryfikuj Swoje Konto poniżej, aby zweryfikować swoje konto.'
            ],

            'subscription' => [
                'subject' => 'E-mail subskrypcji',
                'greeting' => ' Witamy w ' . config('app.name') . ' - Subskrypcji e-mailowej',
                'unsubscribe' => 'Anuluj subskrypcję',
                'summary' => 'Dziękujemy za umieszczenie nas w Twojej skrzynce odbiorczej. Minęło trochę czasu, odkąd czytałeś  ' . config('app.name') . ' E-mail, a my nie chcemy zaśmiecać Twoją skrzynkę. Jeśli nadal nie chcesz odbierać
                 nowych wiadomości marketingowych e-mail, powinieneś klinkąć na przycisk poniżej. ”.'
            ]
        ]
    ],

    'webkul' => [
        'copy-right' => '© Copyright :year Webkul Software, Wszelkie prawa zastrzeżone',
    ],

    'response' => [
        'create-success' => ':name została utworzona pomyślnie.',
        'update-success' => ':name została zaktualizowana pomyślnie.',
        'delete-success' => ':name została usunięta pomyślnie.',
        'submit-success' => ':name została przesłana pomyślnie.'
    ],
];
