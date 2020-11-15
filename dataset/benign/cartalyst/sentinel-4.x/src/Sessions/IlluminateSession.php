<?php

/*
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    4.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Sessions;

use Illuminate\Session\Store as SessionStore;

class IlluminateSession implements SessionInterface
{
    /**
     * The session store object.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Constructor.
     *
     * @param \Illuminate\Session\Store $session
     * @param string                    $key
     *
     * @return void
     */
    public function __construct(SessionStore $session, string $key = null)
    {
        $this->session = $session;

        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function put($value): void
    {
        $this->session->put($this->key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->session->get($this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function forget(): void
    {
        $this->session->forget($this->key);
    }
}
