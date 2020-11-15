<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Tyto přihlašovací údaje neodpovídají žadnému záznamu.',
    'throttle' => 'Příliš mnoho pokusů o přihlášení. Zkuste to prosím znovu za :seconds vteřin.',
    'not_authorized' => 'Nejste oprávněni provést tuto akci',
    'signup_disabled' => 'Nové registrace jsou aktuálně zastaveny',
    'back_homepage' => 'Zpět na domovskou stránku',
    'mfa_auth_otp' => 'Authenticate with your two factor device',
    'mfa_auth_webauthn' => 'Authenticate with a security key (WebAuthn)',
    '2fa_title' => 'Two Factor Authentication',
    '2fa_wrong_validation' => 'The two factor authentication has failed.',
    '2fa_one_time_password' => 'Two factor authentication code',
    '2fa_recuperation_code' => 'Enter a two factor recovery code',
    '2fa_otp_help' => 'Open up your two factor authentication mobile app and copy the code',

    'login_to_account' => 'Login to your account',
    'login_with_recovery' => 'Login with a recovery code',
    'login_again' => 'Please login again to your account',
    'email' => 'Email',
    'password' => 'Password',
    'recovery' => 'Recovery code',
    'login' => 'Login',
    'button_remember' => 'Remember Me',
    'password_forget' => 'Forget your password?',
    'password_reset' => 'Reset your password',
    'use_recovery' => 'Or you can use a <a href=":url">recovery code</a>',
    'signup_no_account' => 'Don’t have an account?',
    'signup' => 'Sign up',
    'create_account' => 'Create the first account by <a href=":url">signing up</a>',
    'change_language_title' => 'Change language:',
    'change_language' => 'Change language to :lang',

    'password_reset_title' => 'Reset Password',
    'password_reset_email' => 'E-Mail Address',
    'password_reset_send_link' => 'Send Password Reset Link',
    'password_reset_password' => 'Password',
    'password_reset_password_confirm' => 'Confirm Password',
    'password_reset_action' => 'Reset Password',
    'password_reset_email_content' => 'Click here to reset your password:',

    'register_title_welcome' => 'Welcome to your newly installed Monica instance',
    'register_create_account' => 'You need to create an account to use Monica',
    'register_title_create' => 'Create your Monica account',
    'register_login' => '<a href=":url">Log in</a> if you already have an account.',
    'register_email' => 'Enter a valid email address',
    'register_email_example' => 'you@home',
    'register_firstname' => 'Jméno',
    'register_firstname_example' => 'např. Jan',
    'register_lastname' => 'Příjmení',
    'register_lastname_example' => 'např. Novák',
    'register_password' => 'Heslo',
    'register_password_example' => 'Zadejte bezpečné heslo',
    'register_password_confirmation' => 'Potvrzení hesla',
    'register_action' => 'Register',
    'register_policy' => 'Signing up signifies you’ve read and agree to our <a href=":url" hreflang=":hreflang">Privacy Policy</a> and <a href=":urlterm" hreflang=":hreflang">Terms of use</a>.',
    'register_invitation_email' => 'For security purposes, please indicate the email of the person who’ve invited you to join this account. This information is provided in the invitation email.',

    'confirmation_title' => 'Verify Your Email Address',
    'confirmation_fresh' => 'A fresh verification link has been sent to your email address.',
    'confirmation_check' => 'Before proceeding, please check your email for a verification link.',
    'confirmation_request_another' => 'If you did not receive the email <a :action>click here to request another</a>.',

    'confirmation_again' => 'If you want to change your email address you can <a href=":url" class="alert-link">click here</a>.',
    'email_change_current_email' => 'Aktuální e-mailová adresa:',
    'email_change_title' => 'Změna e-mailové adresy',
    'email_change_new' => 'Nová e-mailová adresa',
    'email_changed' => 'Your email address has been changed. Check your mailbox to validate it.',
];
