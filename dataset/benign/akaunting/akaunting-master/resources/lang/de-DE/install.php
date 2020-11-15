<?php

return [

    'next'                  => 'Nächste',
    'refresh'               => 'Aktualisieren',

    'steps' => [
        'requirements'      => 'Bitte wenden Sie sich an Ihren Hosting-Dienstleister um die Fehler beheben zu lassen!',
        'language'          => 'Schritt 1/3: Sprachauswahl',
        'database'          => 'Schritt 2/3: Datenbank-Setup',
        'settings'          => 'Schritt 3/3: Unternehmen und Admin-Details',
    ],

    'language' => [
        'select'            => 'Sprache wählen',
    ],

    'requirements' => [
        'enabled'           => ':feature muss aktiviert sein!',
        'disabled'          => ':feature muss deaktiviert sein!',
        'extension'         => ':extension Erweiterung muss installiert und geladen sein!',
        'directory'         => ':directory Verzeichnis muss schreibbar sein!',
        'executable'        => 'Die ausführbare PHP-CLI-Datei ist nicht definiert oder funktioniert nicht oder ihre Version ist nicht :php_version oder höher! Bitten Sie Ihre Hosting-Firma, PHP_BINARY oder PHP_PATH Umgebungsvariable korrekt zu setzen.',
    ],

    'database' => [
        'hostname'          => 'Servername',
        'username'          => 'Benutzername',
        'password'          => 'Passwort',
        'name'              => 'Datenbank',
    ],

    'settings' => [
        'company_name'      => 'Unternehmensname',
        'company_email'     => 'Unternehmens E-Mail-Adresse',
        'admin_email'       => 'Administrator E-Mail-Adresse',
        'admin_password'    => 'Admin Passwort',
    ],

    'error' => [
        'connection'        => 'Fehler: Es konnte keine Verbindung zur Datenbank hergestellt werden! Stellen Sie sicher, dass die Angaben korrekt sind.',
    ],

];
