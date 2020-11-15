<?php

namespace Bolt\Configuration\Validation;

use Bolt\Config;
use Bolt\Configuration\PathResolver;
use Bolt\Exception\BootException;
use Bolt\Logger\FlashLoggerInterface;

/**
 * System validator.
 *
 * @internal For BC. Use of this class should not include use of LowlevelChecks
 *           methods/properties.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Validator implements ValidatorInterface
{
    const CHECK_APACHE = 'apache';
    const CHECK_CACHE = 'cache';
    const CHECK_CONFIG = 'configuration';
    const CHECK_DATABASE = 'database';

    /** @var Config */
    private $configManager;
    /** @var PathResolver */
    private $pathResolver;
    /** @var FlashLoggerInterface */
    private $flashLogger;
    /** @var array */
    private $check = [
        self::CHECK_CONFIG       => Configuration::class,
        self::CHECK_DATABASE     => Database::class,
        self::CHECK_CACHE        => Cache::class,
        self::CHECK_APACHE       => Apache::class,
    ];

    /**
     * Constructor.
     *
     * @param Config               $config
     * @param PathResolver         $pathResolver
     * @param FlashLoggerInterface $flashLogger
     */
    public function __construct(
        Config $config,
        PathResolver $pathResolver,
        FlashLoggerInterface $flashLogger
    ) {
        $this->configManager = $config;
        $this->pathResolver = $pathResolver;
        $this->flashLogger = $flashLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function add($checkName, $className, $prepend = false)
    {
        if ($prepend) {
            $this->check = [$checkName => $className] + $this->check;
        } else {
            $this->check[$checkName] = $className;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($checkName)
    {
        return isset($this->check[$checkName]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($checkName)
    {
        unset($this->check[$checkName]);
    }

    /**
     * {@inheritdoc}
     */
    public function check($checkName)
    {
        $className = $this->check[$checkName];

        return $this
            ->getValidator($className, $checkName)
            ->check()
        ;
    }

    /**
     * Run system installation checks.
     */
    public function checks()
    {
        foreach ($this->check as $checkName => $className) {
            $this
                ->getValidator($className, $checkName)
                ->check()
            ;
        }
    }

    /**
     * Get a validator object from a given class name.
     *
     * @param string|ValidationInterface $className
     * @param mixed                      $constructorArgs
     *
     * @return ValidationInterface
     */
    private function getValidator($className, $constructorArgs)
    {
        /** @var ValidationInterface $validator */
        $validator = is_string($className) ? new $className($constructorArgs) : $className;
        if (!$validator instanceof ValidationInterface) {
            throw new BootException(sprintf('System validator was given a validation class %s that does not implement %s', $className, ValidationInterface::class));
        }
        if ($validator instanceof PathResolverAwareInterface) {
            $validator->setPathResolver($this->pathResolver);
        }
        if ($validator instanceof ConfigAwareInterface) {
            $validator->setConfig($this->configManager);
        }
        if ($validator instanceof FlashLoggerAwareInterface) {
            $validator->setFlashLogger($this->flashLogger);
        }

        return $validator;
    }
}
