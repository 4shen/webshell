<?php declare(strict_types=1);

namespace Packagist\WebBundle\Security;

use Packagist\Redis\FailedLoginCounter;
use Predis\Client;
use Symfony\Component\HttpFoundation\Request;

class RecaptchaHelper
{
    private const LOGIN_BASE_KEY_IP = 'bf:login:ip:';
    private const LOGIN_BASE_KEY_USER = 'bf:login:user:';

    /** @var Client */
    private $redis;
    /** @var bool */
    private $recaptchaEnabled;

    public function __construct(Client $redis, bool $recaptchaEnabled)
    {
        $this->redis = $redis;
        $this->recaptchaEnabled = $recaptchaEnabled;
    }

    public function requiresRecaptcha(string $ip, string $username): bool
    {
        if (!$this->recaptchaEnabled) {
            return false;
        }

        $keys = [self::LOGIN_BASE_KEY_IP . $ip];
        if ($username) {
            $keys[] = self::LOGIN_BASE_KEY_USER . strtolower($username);
        }

        $result = $this->redis->mget($keys);
        foreach ($result as $count) {
            if ($count >= 3) {
                return true;
            }
        }

        return false;
    }

    public function increaseCounter(Request $request): void
    {
        if ($this->recaptchaEnabled) {
            $this->redis->getProfile()->defineCommand('incrFailedLoginCounter', FailedLoginCounter::class);

            $ipKey = self::LOGIN_BASE_KEY_IP . $request->getClientIp();
            $userKey = self::LOGIN_BASE_KEY_USER . strtolower((string) $request->get('_username'));
            $this->redis->incrFailedLoginCounter($ipKey, $userKey);
        }
    }

    public function clearCounter(Request $request): void
    {
        if ($this->recaptchaEnabled) {
            $userKey = self::LOGIN_BASE_KEY_USER . strtolower((string) $request->get('_username'));
            $this->redis->del([$userKey]);
        }
    }
}
