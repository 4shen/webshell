<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use Birke\Rememberme\Authenticator as RememberMe;
use Birke\Rememberme\Triplet as RememberMeTriplet;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountDisabledException;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountInvalidException;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountNotVerifiedException;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthCompromisedException;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthExpiredException;
use UserFrosting\Sprinkle\Account\Authenticate\Exception\InvalidCredentialsException;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Account\Rememberme\PDOStorage as RememberMePDO;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Handles authentication tasks.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * Partially inspired by Laravel's Authentication component: https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/SessionGuard.php
 */
class Authenticator
{
    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Capsule
     */
    protected $db;

    /**
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * @var RememberMePDO
     */
    protected $rememberMeStorage;

    /**
     * @var RememberMe
     */
    protected $rememberMe;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * Indicates if the user was authenticated via a rememberMe cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * Create a new Authenticator object.
     *
     * @param ClassMapper $classMapper Maps generic class identifiers to specific class names.
     * @param Session     $session     The session wrapper object that will store the user's id.
     * @param Config      $config      Config object that contains authentication settings.
     * @param Cache       $cache       Cache service instance
     * @param Capsule     $db          Database service instance
     */
    public function __construct(ClassMapper $classMapper, Session $session, Config $config, Cache $cache, Capsule $db)
    {
        $this->classMapper = $classMapper;
        $this->session = $session;
        $this->config = $config;
        $this->cache = $cache;
        $this->db = $db;

        // Initialize RememberMe storage
        $this->rememberMeStorage = new RememberMePDO($this->db);

        // Set up RememberMe
        $this->rememberMe = new RememberMe($this->rememberMeStorage);

        // Set cookie name
        $cookieName = $this->config['session.name'] . '-' . $this->config['remember_me.cookie.name'];
        $this->rememberMe->getCookie()->setName($cookieName);

        // Change cookie path
        $this->rememberMe->getCookie()->setPath($this->config['remember_me.session.path']);

        // Set expire time, if specified
        if ($this->config->has('remember_me.expire_time') && $this->config->get('remember_me.expire_time') !== null) {
            $this->rememberMe->getCookie()->setExpireTime($this->config['remember_me.expire_time']);
        }

        // Set domain, if specified
        if ($this->config->has('remember_me.domain') && $this->config->has('remember_me.domain') != null) {
            $this->rememberMe->getCookie()->setDomain($this->config['remember_me.domain']);
        }

        $this->user = null;
        $this->viaRemember = false;
    }

    /**
     * Attempts to authenticate a user based on a supplied identity and password.
     *
     * If successful, the user's id is stored in session.
     *
     * @param string $identityColumn
     * @param string $identityValue
     * @param string $password
     * @param bool   $rememberMe
     *
     * @throws InvalidCredentialsException
     * @throws AccountDisabledException
     * @throws AccountNotVerifiedException
     *
     * @return UserInterface
     */
    public function attempt($identityColumn, $identityValue, $password, $rememberMe = false)
    {
        // Try to load the user, using the specified conditions
        $user = $this->classMapper->getClassMapping('user')::where($identityColumn, $identityValue)->first();

        if (!$user) {
            throw new InvalidCredentialsException();
        }

        // Check that the user has a password set (so, rule out newly created accounts without a password)
        if (!$user->password) {
            throw new InvalidCredentialsException();
        }

        // Check that the user's account is enabled
        if ($user->flag_enabled == 0) {
            throw new AccountDisabledException();
        }

        // Check that the user's account is verified (if verification is required)
        if ($this->config['site.registration.require_email_verification'] && $user->flag_verified == 0) {
            throw new AccountNotVerifiedException();
        }

        // Here is my password.  May I please assume the identify of this user now?
        if (Password::verify($password, $user->password)) {
            $this->login($user, $rememberMe);

            return $user;
        } else {
            // We know the password is at fault here (as opposed to the identity), but lets not give away the combination in case of someone bruteforcing
            throw new InvalidCredentialsException();
        }
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest (unauthenticated).
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Process an account login request.
     *
     * This method logs in the specified user, allowing the client to assume the user's identity for the duration of the session.
     *
     * @param UserInterface $user       The user to log in.
     * @param bool          $rememberMe Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
     *
     * @todo Figure out a way to update the currentUser service to reflect the logged-in user *immediately* in the service provider.
     * As it stands, the currentUser service will still reflect a "guest user" for the remainder of the request.
     */
    public function login(UserInterface $user, $rememberMe = false)
    {
        $oldId = session_id();
        $this->session->regenerateId(true);

        // Since regenerateId deletes the old session, we'll do the same in cache
        $this->flushSessionCache($oldId);

        // If the user wants to be remembered, create Rememberme cookie
        if ($rememberMe) {
            $this->rememberMe->createCookie($user->id);
        } else {
            $this->rememberMe->clearCookie();
        }

        // Assume identity
        $key = $this->config['session.keys.current_user_id'];
        $this->session[$key] = $user->id;

        // Set auth mode
        $this->viaRemember = false;

        // User login actions
        $user->onLogin();
    }

    /**
     * Processes an account logout request.
     *
     * Logs the currently authenticated user out, destroying the PHP session and clearing the persistent session.
     * This can optionally remove persistent sessions across all browsers/devices, since there can be a "RememberMe" cookie
     * and corresponding database entries in multiple browsers/devices.  See http://jaspan.com/improved_persistent_login_cookie_best_practice.
     *
     * @param bool $complete If set to true, will ensure that the user is logged out from *all* browsers on all devices.
     */
    public function logout($complete = false)
    {
        $currentUserId = $this->session->get($this->config['session.keys.current_user_id']);

        // This removes all of the user's persistent logins from the database
        if ($complete) {
            $this->rememberMeStorage->cleanAllTriplets($currentUserId);
        }

        // Clear the rememberMe cookie
        $this->rememberMe->clearCookie();

        // User logout actions
        if ($currentUserId) {
            $currentUser = $this->classMapper->getClassMapping('user')::find($currentUserId);
            if ($currentUser) {
                $currentUser->onLogout();
            }

            // Delete user object cache
            $this->cache->forget($this->config['cache.user.key'] . $currentUserId);
        }

        $this->user = null;
        $this->loggedOut = true;

        $oldId = session_id();

        // Completely destroy the session
        $this->session->destroy();

        // Since regenerateId deletes the old session, we'll do the same in cache
        $this->flushSessionCache($oldId);

        // Restart the session service
        $this->session->start();
    }

    /**
     * Try to get the currently authenticated user, returning a guest user if none was found.
     *
     * Tries to re-establish a session for "remember-me" users who have been logged out due to an expired session.
     *
     * @throws AuthExpiredException
     * @throws AuthCompromisedException
     * @throws AccountInvalidException
     * @throws AccountDisabledException
     *
     * @return UserInterface|null
     */
    public function user()
    {
        $user = null;

        if (!$this->loggedOut) {

            // Return any cached user
            if (!is_null($this->user)) {
                return $this->user;
            }

            // If this throws a PDOException we catch it and return null than allowing the exception to propagate.
            // This is because the error handler relies on Twig, which relies on a Twig Extension, which relies on the global current_user variable.
            // So, we really don't want this method to throw any database exceptions.
            try {
                // Now, check to see if we have a user in session
                $user = $this->loginSessionUser();

                // If no user was found in the session, try to login via RememberMe cookie
                if (!$user) {
                    $user = $this->loginRememberedUser();
                }
            } catch (\PDOException $e) {
                $user = null;
            }
        }

        return $this->user = $user;
    }

    /**
     * Determine whether the current user was authenticated using a remember me cookie.
     *
     * This function is useful when users are performing sensitive operations, and you may want to force them to re-authenticate.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * Attempt to log in the client from their rememberMe token (in their cookie).
     *
     * @throws AuthCompromisedException The client attempted to log in with an invalid rememberMe token.
     *
     * @return UserInterface|bool If successful, the User object of the remembered user.  Otherwise, return false.
     */
    protected function loginRememberedUser()
    {
        /** @var \Birke\Rememberme\LoginResult $loginResult */
        $loginResult = $this->rememberMe->login();

        if ($loginResult->isSuccess()) {
            // Update in session
            $this->session[$this->config['session.keys.current_user_id']] = $loginResult->getCredential();
            // There is a chance that an attacker has stolen the login token,
            // so we store the fact that the user was logged in via RememberMe (instead of login form)
            $this->viaRemember = true;
        } else {
            // If $rememberMe->login() was not successfull, check if the token was invalid as well.  This means the cookie was stolen.
            if ($loginResult->hasPossibleManipulation()) {
                throw new AuthCompromisedException();
            }
        }

        return $this->validateUserAccount($loginResult->getCredential());
    }

    /**
     * Attempt to log in the client from the session.
     *
     * @throws AuthExpiredException The client attempted to use an expired rememberMe token.
     *
     * @return UserInterface|null If successful, the User object of the user in session.  Otherwise, return null.
     */
    protected function loginSessionUser()
    {
        $userId = $this->session->get($this->config['session.keys.current_user_id']);

        // If a user_id was found in the session, check any rememberMe cookie that was submitted.
        // If they submitted an expired rememberMe cookie, then we need to log them out.
        if ($userId) {
            if (!$this->validateRememberMeCookie()) {
                $this->logout();

                throw new AuthExpiredException();
            }
        }

        return $this->validateUserAccount($userId);
    }

    /**
     * Determine if the cookie contains a valid rememberMe token.
     *
     * @return bool
     */
    protected function validateRememberMeCookie()
    {
        $cookieValue = $this->rememberMe->getCookie()->getValue();
        if (!$cookieValue) {
            return true;
        }
        $triplet = RememberMeTriplet::fromString($cookieValue);
        if (!$triplet->isValid()) {
            return false;
        }

        return true;
    }

    /**
     * Tries to load the specified user by id from the database.
     *
     * Checks that the account is valid and enabled, throwing an exception if not.
     *
     * @param int $userId
     *
     * @throws AccountInvalidException
     * @throws AccountDisabledException
     *
     * @return UserInterface|null
     */
    protected function validateUserAccount($userId)
    {
        if ($userId) {

            // Load user from db, cache the result
            $key = $this->config['cache.user.key'] . $userId;
            $user = $this->cache->remember($key, $this->config['cache.user.delay'] * 60, function () use ($userId) {
                return $this->classMapper->getClassMapping('user')::find((int) $userId);
            });

            // If the user doesn't exist any more, throw an exception.
            if (!$user) {
                throw new AccountInvalidException();
            }

            // If the user has been disabled since their last request, throw an exception.
            if (!$user->flag_enabled) {
                throw new AccountDisabledException();
            }

            return $user;
        } else {
            return;
        }
    }

    /**
     * Flush the cache associated with a session id.
     *
     * @param string $id The session id
     *
     * @return bool
     */
    public function flushSessionCache($id)
    {
        return $this->cache->tags('_s' . $id)->flush();
    }
}
