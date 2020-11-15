<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Account\Registration;
use UserFrosting\Sprinkle\Account\Controller\Exception\SpammyRequestException;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Account\Util\Util as AccountUtil;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Sprinkle\Core\Util\Captcha;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;

/**
 * Controller class for /account/* URLs.  Handles account-related activities, including login, registration, password recovery, and account settings.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 *
 * @see http://www.userfrosting.com/navigating/#structure
 */
class AccountController extends SimpleController
{
    /**
     * Check a username for availability.
     *
     * This route is throttled by default, to discourage abusing it for account enumeration.
     * This route is "public access".
     *
     * AuthGuard: false
     * Route: /account/check-username
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws BadRequestException
     */
    public function checkUsername(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        // Load request schema
        $schema = new RequestSchema('schema://requests/check-username.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException('Missing or malformed request data!');
            foreach ($validator->errors() as $idx => $field) {
                foreach ($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }

            throw $e;
        }

        /** @var \UserFrosting\Sprinkle\Core\Throttle\Throttler $throttler */
        $throttler = $this->ci->throttler;
        $delay = $throttler->getDelay('check_username_request');

        // Throttle requests
        if ($delay > 0) {
            return $response->withJson([], 429);
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\I18n\Translator $translator */
        $translator = $this->ci->translator;

        // Log throttleable event
        $throttler->logEvent('check_username_request');

        if ($classMapper->getClassMapping('user')::findUnique($data['user_name'], 'user_name')) {
            $message = $translator->translate('USERNAME.NOT_AVAILABLE', $data);

            return $response->write($message)->withStatus(200);
        } else {
            return $response->write('true')->withStatus(200);
        }
    }

    /**
     * Processes a request to cancel a password reset request.
     *
     * This is provided so that users can cancel a password reset request, if they made it in error or if it was not initiated by themselves.
     * Processes the request from the password reset link, checking that:
     * 1. The provided token is associated with an existing user account, who has a pending password reset request.
     *
     * AuthGuard: false
     * Route: /account/set-password/deny
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function denyResetPassword(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $loginPage = $this->ci->router->pathFor('login');

        // Load validation rules
        $schema = new RequestSchema('schema://requests/deny-password.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  Since this is a GET request, we need to redirect on failure
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withRedirect($loginPage);
        }

        /** @var \UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository $passwordReset */
        $passwordReset = $this->ci->repoPasswordReset->cancel($data['token']);

        if (!$passwordReset) {
            $ms->addMessageTranslated('danger', 'PASSWORD.FORGET.INVALID');

            return $response->withRedirect($loginPage);
        }

        $ms->addMessageTranslated('success', 'PASSWORD.FORGET.REQUEST_CANNED');

        return $response->withRedirect($loginPage);
    }

    /**
     * Processes a request to email a forgotten password reset link to the user.
     *
     * Processes the request from the form on the "forgot password" page, checking that:
     * 1. The rate limit for this type of request is being observed.
     * 2. The provided email address belongs to a registered account;
     * 3. The submitted data is valid.
     * Note that we have removed the requirement that a password reset request not already be in progress.
     * This is because we need to allow users to re-request a reset, even if they lose the first reset email.
     * This route is "public access".
     *
     * @todo require additional user information
     * @todo prevent password reset requests for root account?
     *
     * AuthGuard: false
     * Route: /account/forgot-password
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function forgotPassword(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Get POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/forgot-password.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  Failed validation attempts do not count towards throttling limit.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withJson([], 400);
        }

        // Throttle requests
        /** @var \UserFrosting\Sprinkle\Core\Throttle\Throttler $throttler */
        $throttler = $this->ci->throttler;

        $throttleData = [
            'email' => $data['email'],
        ];
        $delay = $throttler->getDelay('password_reset_request', $throttleData);

        if ($delay > 0) {
            $ms->addMessageTranslated('danger', 'RATE_LIMIT_EXCEEDED', ['delay' => $delay]);

            return $response->withJson([], 429);
        }

        // All checks passed!  log events/activities, update user, and send email
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $data, $throttler, $throttleData, $config) {

            // Log throttleable event
            $throttler->logEvent('password_reset_request', $throttleData);

            // Load the user, by email address
            $user = $classMapper->getClassMapping('user')::where('email', $data['email'])->first();

            // Check that the email exists.
            // If there is no user with that email address, we should still pretend like we succeeded, to prevent account enumeration
            if ($user) {
                // Try to generate a new password reset request.
                // Use timeout for "reset password"
                $passwordReset = $this->ci->repoPasswordReset->create($user, $config['password_reset.timeouts.reset']);

                // Create and send email
                $message = new TwigMailMessage($this->ci->view, 'mail/password-reset.html.twig');
                $message->from($config['address_book.admin'])
                        ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                        ->addParams([
                            'user'         => $user,
                            'token'        => $passwordReset->getToken(),
                            'request_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);

                $this->ci->mailer->send($message);
            }
        });

        // TODO: create delay to prevent timing-based attacks

        $ms->addMessageTranslated('success', 'PASSWORD.FORGET.REQUEST_SENT', ['email' => $data['email']]);

        return $response->withJson([], 200);
    }

    /**
     * Returns a modal containing account terms of service.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     *
     * AuthGuard: false
     * Route: /modals/account/tos
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function getModalAccountTos(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'modals/tos.html.twig');
    }

    /**
     * Generate a random captcha, store it to the session, and return the captcha image.
     *
     * AuthGuard: false
     * Route: /account/captcha
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function imageCaptcha(Request $request, Response $response, $args)
    {
        $captcha = new Captcha($this->ci->session, $this->ci->config['session.keys.captcha']);
        $captcha->generateRandomCode();

        return $response->withStatus(200)
                    ->withHeader('Content-Type', 'image/png;base64')
                    ->write($captcha->getImage());
    }

    /**
     * Processes an account login request.
     *
     * Processes the request from the form on the login page, checking that:
     * 1. The user is not already logged in.
     * 2. The rate limit for this type of request is being observed.
     * 3. Email login is enabled, if an email address was used.
     * 4. The user account exists.
     * 5. The user account is enabled and verified.
     * 6. The user entered a valid username/email and password.
     * This route, by definition, is "public access".
     *
     * AuthGuard: false
     * Route: /account/login
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function login(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        // Return 200 success if user is already logged in
        if ($authenticator->check()) {
            $ms->addMessageTranslated('warning', 'LOGIN.ALREADY_COMPLETE');

            return $response->withJson([], 200);
        }

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Get POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/login.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  Failed validation attempts do not count towards throttling limit.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withJson([], 400);
        }

        // Determine whether we are trying to log in with an email address or a username
        $isEmail = filter_var($data['user_name'], FILTER_VALIDATE_EMAIL);

        // Throttle requests

        /** @var \UserFrosting\Sprinkle\Core\Throttle\Throttler $throttler */
        $throttler = $this->ci->throttler;

        $userIdentifier = $data['user_name'];

        $throttleData = [
            'user_identifier' => $userIdentifier,
        ];

        $delay = $throttler->getDelay('sign_in_attempt', $throttleData);
        if ($delay > 0) {
            $ms->addMessageTranslated('danger', 'RATE_LIMIT_EXCEEDED', [
                'delay' => $delay,
            ]);

            return $response->withJson([], 429);
        }

        // If credential is an email address, but email login is not enabled, raise an error.
        // Note that this error counts towards the throttling limit.
        if ($isEmail && !$config['site.login.enable_email']) {
            $ms->addMessageTranslated('danger', 'USER_OR_PASS_INVALID');
            $throttler->logEvent('sign_in_attempt', $throttleData);

            return $response->withJson([], 403);
        }

        // Try to authenticate the user.  Authenticator will throw an exception on failure.
        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        try {
            $currentUser = $authenticator->attempt(($isEmail ? 'email' : 'user_name'), $userIdentifier, $data['password'], $data['rememberme']);
        } catch (\Exception $e) {
            // only let unsuccessful logins count toward the throttling limit
            $throttler->logEvent('sign_in_attempt', $throttleData);

            throw $e;
        }

        $ms->addMessageTranslated('success', 'WELCOME', $currentUser->export());

        // Set redirect, if relevant
        $redirectOnLogin = $this->ci->get('redirect.onLogin');

        return $redirectOnLogin($request, $response, $args);
    }

    /**
     * Log the user out completely, including destroying any "remember me" token.
     *
     * AuthGuard: true
     * Route: /account/logout
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function logout(Request $request, Response $response, $args)
    {
        // Destroy the session
        $this->ci->authenticator->logout();

        // Return to home page
        $config = $this->ci->config;

        return $response->withStatus(302)->withHeader('Location', $config['site.uri.public']);
    }

    /**
     * Render the "forgot password" page.
     *
     * This creates a simple form to allow users who forgot their password to have a time-limited password reset link emailed to them.
     * By default, this is a "public page" (does not require authentication).
     *
     * AuthGuard: false
     * Route: /account/forgot-password
     * Route Name: forgot-password
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageForgotPassword(Request $request, Response $response, $args)
    {
        // Load validation rules
        $schema = new RequestSchema('schema://requests/forgot-password.yaml');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'pages/forgot-password.html.twig', [
            'page' => [
                'validators' => [
                    'forgot_password'    => $validator->rules('json', false),
                ],
            ],
        ]);
    }

    /**
     * Render the account registration page for UserFrosting.
     *
     * This allows new (non-authenticated) users to create a new account for themselves on your website (if enabled).
     * By definition, this is a "public page" (does not require authentication).
     *
     * AuthGuard: false
     * checkEnvironment
     * Route: /account/register
     * Route Name: register
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws NotFoundException If site registration is disabled
     */
    public function pageRegister(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        if (!$config['site.registration.enabled']) {
            throw new NotFoundException();
        }

        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        // Redirect if user is already logged in
        if ($authenticator->check()) {
            $redirect = $this->ci->get('redirect.onAlreadyLoggedIn');

            return $redirect($request, $response, $args);
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests/register.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);
        $validatorRegister = new JqueryValidationAdapter($schema, $this->ci->translator);

        // Get locale information
        $currentLocale = $this->ci->translator->getLocale()->getIdentifier();

        // Get a list of all locales
        $locales = $this->ci->locale->getAvailableOptions();

        // Hide the locale field if there is only 1 locale available
        $fields = [
            'hidden'   => [],
            'disabled' => [],
        ];
        if (count($locales) <= 1) {
            $fields['hidden'][] = 'locale';
        }

        return $this->ci->view->render($response, 'pages/register.html.twig', [
            'page' => [
                'validators' => [
                    'register' => $validatorRegister->rules('json', false),
                ],
            ],
            'fields'  => $fields,
            'locales' => [
                'available' => $locales,
                'current'   => $currentLocale,
            ],
        ]);
    }

    /**
     * Render the "resend verification email" page.
     *
     * This is a form that allows users who lost their account verification link to have the link resent to their email address.
     * By default, this is a "public page" (does not require authentication).
     *
     * AuthGuard: false
     * Route: /account/resend-verification
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageResendVerification(Request $request, Response $response, $args)
    {
        // Load validation rules
        $schema = new RequestSchema('schema://requests/resend-verification.yaml');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'pages/resend-verification.html.twig', [
            'page' => [
                'validators' => [
                    'resend_verification'    => $validator->rules('json', false),
                ],
            ],
        ]);
    }

    /**
     * Reset password page.
     *
     * Renders the new password page for password reset requests.
     *
     * AuthGuard: false
     * Route: /account/set-password/confirm
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageResetPassword(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Insert the user's secret token from the link into the password reset form
        $params = $request->getQueryParams();

        // Load validation rules - note this uses the same schema as "set password"
        $schema = new RequestSchema('schema://requests/set-password.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'pages/reset-password.html.twig', [
            'page' => [
                'validators' => [
                    'set_password'    => $validator->rules('json', false),
                ],
            ],
            'token' => isset($params['token']) ? $params['token'] : '',
        ]);
    }

    /**
     * Render the "set password" page.
     *
     * Renders the page where new users who have had accounts created for them by another user, can set their password.
     * By default, this is a "public page" (does not require authentication).
     *
     * AuthGuard: false
     * Route:
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageSetPassword(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Insert the user's secret token from the link into the password set form
        $params = $request->getQueryParams();

        // Load validation rules
        $schema = new RequestSchema('schema://requests/set-password.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'pages/set-password.html.twig', [
            'page' => [
                'validators' => [
                    'set_password'    => $validator->rules('json', false),
                ],
            ],
            'token' => isset($params['token']) ? $params['token'] : '',
        ]);
    }

    /**
     * Account settings page.
     *
     * Provides a form for users to modify various properties of their account, such as name, email, locale, etc.
     * Any fields that the user does not have permission to modify will be automatically disabled.
     * This page requires authentication.
     *
     * AuthGuard: true
     * Route: /account/settings
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function pageSettings(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            throw new ForbiddenException();
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests/account-settings.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);
        $validatorAccountSettings = new JqueryValidationAdapter($schema, $this->ci->translator);

        $schema = new RequestSchema('schema://requests/profile-settings.yaml');
        $validatorProfileSettings = new JqueryValidationAdapter($schema, $this->ci->translator);

        // Get a list of all locales
        $locales = $this->ci->locale->getAvailableOptions();

        // Hide the locale field if there is only 1 locale available
        $fields = [
            'hidden'   => [],
            'disabled' => [],
        ];
        if (count($locales) <= 1) {
            $fields['hidden'][] = 'locale';
        }

        return $this->ci->view->render($response, 'pages/account-settings.html.twig', [
            'locales' => $locales,
            'fields'  => $fields,
            'page'    => [
                'validators' => [
                    'account_settings'    => $validatorAccountSettings->rules('json', false),
                    'profile_settings'    => $validatorProfileSettings->rules('json', false),
                ],
                'visibility' => ($authorizer->checkAccess($currentUser, 'update_account_settings') ? '' : 'disabled'),
            ],
        ]);
    }

    /**
     * Render the account sign-in page for UserFrosting.
     *
     * This allows existing users to sign in.
     * By definition, this is a "public page" (does not require authentication).
     *
     * AuthGuard: false
     * checkEnvironment
     * Route: /account/sign-in
     * Route Name: login
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function pageSignIn(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        // Redirect if user is already logged in
        if ($authenticator->check()) {
            $redirect = $this->ci->get('redirect.onAlreadyLoggedIn');

            return $redirect($request, $response, $args);
        }

        // Load validation rules
        $schema = new RequestSchema('schema://requests/login.yaml');
        $validatorLogin = new JqueryValidationAdapter($schema, $this->ci->translator);

        return $this->ci->view->render($response, 'pages/sign-in.html.twig', [
            'page' => [
                'validators' => [
                    'login'    => $validatorLogin->rules('json', false),
                ],
            ],
        ]);
    }

    /**
     * Processes a request to update a user's profile information.
     *
     * Processes the request from the user profile settings form, checking that:
     * 1. They have the necessary permissions to update the posted field(s);
     * 2. The submitted data is valid.
     * This route requires authentication.
     *
     * AuthGuard: true
     * Route: /account/settings/profile
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function profile(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access control for entire resource - check that the current user has permission to modify themselves
        // See recipe "per-field access control" for dynamic fine-grained control over which properties a user can modify.
        if (!$authorizer->checkAccess($currentUser, 'update_account_settings')) {
            $ms->addMessageTranslated('danger', 'ACCOUNT.ACCESS_DENIED');

            return $response->withJson([], 403);
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/profile-settings.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Get locales
        $locales = $this->ci->locale->getAvailableIdentifiers();

        // Ensure that in the case of using a single locale, that the locale is set
        if (count($locales) <= 1) {
            $data['locale'] = $currentUser->locale;
        }

        // Validate, and halt on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        // Check that locale is valid
        if (isset($data['locale']) && !in_array($data['locale'], $locales)) {
            $ms->addMessageTranslated('danger', 'LOCALE.INVALID', $data);
            $error = true;
        }

        if ($error) {
            return $response->withJson([], 400);
        }

        // Looks good, let's update with new values!
        // Note that only fields listed in `profile-settings.yaml` will be permitted in $data, so this prevents the user from updating all columns in the DB
        $currentUser->fill($data);

        $currentUser->save();

        // Create activity record
        $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated their profile settings.", [
            'type' => 'update_profile_settings',
        ]);

        $ms->addMessageTranslated('success', 'PROFILE.UPDATED');

        return $response->withJson([], 200);
    }

    /**
     * Processes an new account registration request.
     *
     * This is throttled to prevent account enumeration, since it needs to divulge when a username/email has been used.
     * Processes the request from the form on the registration page, checking that:
     * 1. The honeypot was not modified;
     * 2. The master account has already been created (during installation);
     * 3. Account registration is enabled;
     * 4. The user is not already logged in;
     * 5. Valid information was entered;
     * 6. The captcha, if enabled, is correct;
     * 7. The username and email are not already taken.
     * Automatically sends an activation link upon success, if account activation is enabled.
     * This route is "public access".
     * Returns the User Object for the user record that was created.
     *
     * AuthGuard: false
     * Route: /account/register
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @throws SpammyRequestException
     */
    public function register(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Get POST parameters: user_name, first_name, last_name, email, password, passwordc, captcha, spiderbro, csrf_token
        $params = $request->getParsedBody();

        // Check the honeypot. 'spiderbro' is not a real field, it is hidden on the main page and must be submitted with its default value for this to be processed.
        if (!isset($params['spiderbro']) || $params['spiderbro'] != 'http://') {
            throw new SpammyRequestException('Possible spam received:' . print_r($params, true));
        }

        // Security measure: do not allow registering new users until the master account has been created.
        if (!$classMapper->getClassMapping('user')::find($config['reserved_user_ids.master'])) {
            $ms->addMessageTranslated('danger', 'ACCOUNT.MASTER_NOT_EXISTS');

            return $response->withJson([], 403);
        }

        // Check if registration is currently enabled
        if (!$config['site.registration.enabled']) {
            $ms->addMessageTranslated('danger', 'REGISTRATION.DISABLED');

            return $response->withJson([], 403);
        }

        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        // Prevent the user from registering if he/she is already logged in
        if ($authenticator->check()) {
            $ms->addMessageTranslated('danger', 'REGISTRATION.LOGOUT');

            return $response->withJson([], 403);
        }

        // Load the request schema
        $schema = new RequestSchema('schema://requests/register.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Ensure that in the case of using a single locale, that the locale is set
        if (count($this->ci->locale->getAvailableIdentifiers()) <= 1) {
            $data['locale'] = $config['site.registration.user_defaults.locale'];
        }

        // Validate request data
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        /** @var \UserFrosting\Sprinkle\Core\Throttle\Throttler $throttler */
        $throttler = $this->ci->throttler;
        $delay = $throttler->getDelay('registration_attempt');

        // Throttle requests
        if ($delay > 0) {
            return $response->withJson([], 429);
        }

        // Check captcha, if required
        if ($config['site.registration.captcha']) {
            $captcha = new Captcha($this->ci->session, $this->ci->config['session.keys.captcha']);
            if (!isset($data['captcha']) || !$captcha->verifyCode($data['captcha'])) {
                $ms->addMessageTranslated('danger', 'CAPTCHA.FAIL');
                $error = true;
            }
        }

        if ($error) {
            return $response->withJson([], 400);
        }

        // Remove captcha, password confirmation from object data after validation
        unset($data['captcha']);
        unset($data['passwordc']);

        // Now that we check the form, we can register the actual user
        $registration = new Registration($this->ci, $data);

        // Try registration. An HttpException will be thrown if it fails
        // No need to catch, as this kind of exception will automatically returns the addMessageTranslated
        $user = $registration->register();

        // Success message
        if ($config['site.registration.require_email_verification']) {
            // Verification required
            $ms->addMessageTranslated('success', 'REGISTRATION.COMPLETE_TYPE2', $user->toArray());
        } else {
            // No verification required
            $ms->addMessageTranslated('success', 'REGISTRATION.COMPLETE_TYPE1');
        }

        return $response->withJson([], 200);
    }

    /**
     * Processes a request to resend the verification email for a new user account.
     *
     * Processes the request from the resend verification email form, checking that:
     * 1. The rate limit on this type of request is observed;
     * 2. The provided email is associated with an existing user account;
     * 3. The user account is not already verified;
     * 4. The submitted data is valid.
     * This route is "public access".
     *
     * AuthGuard: false
     * Route: /account/resend-verification
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function resendVerification(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Get POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/resend-verification.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  Failed validation attempts do not count towards throttling limit.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withJson([], 400);
        }

        // Throttle requests

        /** @var \UserFrosting\Sprinkle\Core\Throttle\Throttler $throttler */
        $throttler = $this->ci->throttler;

        $throttleData = [
            'email' => $data['email'],
        ];
        $delay = $throttler->getDelay('verification_request', $throttleData);

        if ($delay > 0) {
            $ms->addMessageTranslated('danger', 'RATE_LIMIT_EXCEEDED', ['delay' => $delay]);

            return $response->withJson([], 429);
        }

        // All checks passed!  log events/activities, create user, and send verification email (if required)
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $data, $throttler, $throttleData, $config) {
            // Log throttleable event
            $throttler->logEvent('verification_request', $throttleData);

            // Load the user, by email address
            $user = $classMapper->getClassMapping('user')::where('email', $data['email'])->first();

            // Check that the user exists and is not already verified.
            // If there is no user with that email address, or the user exists and is already verified,
            // we pretend like we succeeded to prevent account enumeration
            if ($user && $user->flag_verified != '1') {
                // We're good to go - record user activity and send the email
                $verification = $this->ci->repoVerification->create($user, $config['verification.timeout']);

                // Create and send verification email
                $message = new TwigMailMessage($this->ci->view, 'mail/resend-verification.html.twig');

                $message->from($config['address_book.admin'])
                        ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                        ->addParams([
                            'user'  => $user,
                            'token' => $verification->getToken(),
                        ]);

                $this->ci->mailer->send($message);
            }
        });

        $ms->addMessageTranslated('success', 'ACCOUNT.VERIFICATION.NEW_LINK_SENT', ['email' => $data['email']]);

        return $response->withJson([], 200);
    }

    /**
     * Processes a request to set the password for a new or current user.
     *
     * Processes the request from the password create/reset form, which should have the secret token embedded in it, checking that:
     * 1. The provided secret token is associated with an existing user account;
     * 2. The user has a password set/reset request in progress;
     * 3. The token has not expired;
     * 4. The submitted data (new password) is valid.
     * This route is "public access".
     *
     * AuthGuard: false
     * Route: /account/set-password
     * Route Name: {none}
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function setPassword(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Get POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/set-password.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  Failed validation attempts do not count towards throttling limit.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withJson([], 400);
        }

        $forgotPasswordPage = $this->ci->router->pathFor('forgot-password');

        // Ok, try to complete the request with the specified token and new password
        $passwordReset = $this->ci->repoPasswordReset->complete($data['token'], [
            'password' => $data['password'],
        ]);

        if (!$passwordReset) {
            $ms->addMessageTranslated('danger', 'PASSWORD.FORGET.INVALID', ['url' => $forgotPasswordPage]);

            return $response->withJson([], 400);
        }

        $ms->addMessageTranslated('success', 'PASSWORD.UPDATED');

        /** @var \UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;

        // Log out any existing user, and create a new session
        if ($authenticator->check()) {
            $authenticator->logout();
        }

        // Auto-login the user (without "remember me")
        $user = $passwordReset->user;
        $authenticator->login($user);

        $ms->addMessageTranslated('success', 'WELCOME', $user->export());

        return $response->withJson([], 200);
    }

    /**
     * Processes a request to update a user's account information.
     *
     * Processes the request from the user account settings form, checking that:
     * 1. The user correctly input their current password;
     * 2. They have the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     *
     * AuthGuard: true
     * Route: /account/settings
     * Route Name: settings
     * Request type: POST
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function settings(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access control for entire resource - check that the current user has permission to modify themselves
        // See recipe "per-field access control" for dynamic fine-grained control over which properties a user can modify.
        if (!$authorizer->checkAccess($currentUser, 'update_account_settings')) {
            $ms->addMessageTranslated('danger', 'ACCOUNT.ACCESS_DENIED');

            return $response->withJson([], 403);
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // POST parameters
        $params = $request->getParsedBody();

        // Load the request schema
        $schema = new RequestSchema('schema://requests/account-settings.yaml');
        $schema->set('password.validators.length.min', $config['site.password.length.min']);
        $schema->set('password.validators.length.max', $config['site.password.length.max']);
        $schema->set('passwordc.validators.length.min', $config['site.password.length.min']);
        $schema->set('passwordc.validators.length.max', $config['site.password.length.max']);

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        $error = false;

        // Ensure that in the case of using a single locale, that the locale is set
        if (count($this->ci->locale->getAvailableIdentifiers()) <= 1) {
            $data['locale'] = $currentUser->locale;
        }

        // Validate, and halt on validation errors.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);
            $error = true;
        }

        // Confirm current password
        if (!isset($data['passwordcheck']) || !Password::verify($data['passwordcheck'], $currentUser->password)) {
            $ms->addMessageTranslated('danger', 'PASSWORD.INVALID');
            $error = true;
        }

        // Remove password check, password confirmation from object data after validation
        unset($data['passwordcheck']);
        unset($data['passwordc']);

        // If new email was submitted, check that the email address is not in use
        if (isset($data['email']) && $data['email'] != $currentUser->email && $classMapper->getClassMapping('user')::findUnique($data['email'], 'email')) {
            $ms->addMessageTranslated('danger', 'EMAIL.IN_USE', $data);
            $error = true;
        }

        if ($error) {
            return $response->withJson([], 400);
        }

        // Hash new password, if specified
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Password::hash($data['password']);
        } else {
            // Do not pass to model if no password is specified
            unset($data['password']);
        }

        // Looks good, let's update with new values!
        // Note that only fields listed in `account-settings.yaml` will be permitted in $data, so this prevents the user from updating all columns in the DB
        $currentUser->fill($data);

        $currentUser->save();

        // Create activity record
        $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated their account settings.", [
            'type' => 'update_account_settings',
        ]);

        $ms->addMessageTranslated('success', 'ACCOUNT.SETTINGS.UPDATED');

        return $response->withJson([], 200);
    }

    /**
     * Suggest an available username for a specified first/last name.
     *
     * This route is "public access".
     *
     * @todo Can this route be abused for account enumeration?  If so we should throttle it as well.
     *
     * AuthGuard: false
     * Route: /account/suggest-username
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function suggestUsername(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $suggestion = AccountUtil::randomUniqueUsername($classMapper, 50, 10);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson([
            'user_name' => $suggestion,
        ], 200, JSON_PRETTY_PRINT);
    }

    /**
     * Processes an new email verification request.
     *
     * Processes the request from the email verification link that was emailed to the user, checking that:
     * 1. The token provided matches a user in the database;
     * 2. The user account is not already verified;
     * This route is "public access".
     *
     * AuthGuard: false
     * Route: /account/verify
     * Route Name: {none}
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     */
    public function verify(Request $request, Response $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        $loginPage = $this->ci->router->pathFor('login');

        // GET parameters
        $params = $request->getQueryParams();

        // Load request schema
        $schema = new RequestSchema('schema://requests/account-verify.yaml');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  This is a GET request, so we redirect on validation error.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            $ms->addValidationErrors($validator);

            return $response->withRedirect($loginPage);
        }

        $verification = $this->ci->repoVerification->complete($data['token']);

        if (!$verification) {
            $ms->addMessageTranslated('danger', 'ACCOUNT.VERIFICATION.TOKEN_NOT_FOUND');

            return $response->withRedirect($loginPage);
        }

        $ms->addMessageTranslated('success', 'ACCOUNT.VERIFICATION.COMPLETE');

        // Forward to login page
        return $response->withRedirect($loginPage);
    }
}
