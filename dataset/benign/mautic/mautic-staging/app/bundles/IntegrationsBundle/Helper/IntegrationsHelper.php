<?php

declare(strict_types=1);

namespace Mautic\IntegrationsBundle\Helper;

use Mautic\IntegrationsBundle\Event\KeysDecryptionEvent;
use Mautic\IntegrationsBundle\Event\KeysEncryptionEvent;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\IntegrationsBundle\Facade\EncryptionService;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Mautic\IntegrationsBundle\IntegrationEvents;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Entity\IntegrationRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IntegrationsHelper
{
    /**
     * @var IntegrationInterface[]
     */
    private $integrations = [];

    /**
     * @var IntegrationRepository
     */
    private $integrationRepository;

    /**
     * @var EncryptionService
     */
    private $encryptionService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $decryptedIntegrationConfigurations = [];

    /**
     * IntegrationsHelper constructor.
     */
    public function __construct(
        IntegrationRepository $integrationRepository,
        EncryptionService $encryptionService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->integrationRepository = $integrationRepository;
        $this->encryptionService     = $encryptionService;
        $this->eventDispatcher       = $eventDispatcher;
    }

    public function addIntegration(IntegrationInterface $integration): void
    {
        $this->integrations[$integration->getName()] = $integration;
    }

    /**
     * @return IntegrationInterface
     *
     * @throws IntegrationNotFoundException
     */
    public function getIntegration(string $integration)
    {
        if (!isset($this->integrations[$integration])) {
            throw new IntegrationNotFoundException("$integration either doesn't exist or has not been tagged with mautic.basic_integration");
        }

        // Ensure the configuration is hydrated
        $this->getIntegrationConfiguration($this->integrations[$integration]);

        return $this->integrations[$integration];
    }

    public function saveIntegrationConfiguration(Integration $configuration): void
    {
        // Encrypt the keys before saving
        $decryptedApiKeys = $configuration->getApiKeys();

        // Dispatch event before encryption
        $encryptionEvent = new KeysEncryptionEvent($configuration, $decryptedApiKeys);
        $this->eventDispatcher->dispatch(IntegrationEvents::INTEGRATION_KEYS_BEFORE_ENCRYPTION, $encryptionEvent);

        // Encrypt and store the keys
        $encryptedApiKeys = $this->encryptionService->encrypt($encryptionEvent->getKeys());
        $configuration->setApiKeys($encryptedApiKeys);

        // Save
        $this->integrationRepository->saveEntity($configuration);

        // Restore decrypted for use
        $configuration->setApiKeys($decryptedApiKeys);
    }

    /**
     * @return Integration
     *
     * @throws IntegrationNotFoundException
     */
    public function getIntegrationConfiguration(IntegrationInterface $integration)
    {
        if (!$integration->hasIntegrationConfiguration()) {
            /** @var Integration $configuration */
            $configuration = $this->integrationRepository->findOneBy(['name' => $integration->getName()]);

            if (!$configuration) {
                throw new IntegrationNotFoundException("{$integration->getName()} doesn't exist in the database");
            }

            $integration->setIntegrationConfiguration($configuration);
        }

        // Make sure the keys are decrypted
        if (!isset($this->decryptedIntegrationConfigurations[$integration->getName()])) {
            $configuration    = $integration->getIntegrationConfiguration();
            $encryptedApiKeys = $configuration->getApiKeys();
            $decryptedApiKeys = $this->encryptionService->decrypt($encryptedApiKeys);

            // Dispatch event after decryption
            $decryptionEvent = new KeysDecryptionEvent($configuration, $decryptedApiKeys);
            $this->eventDispatcher->dispatch(IntegrationEvents::INTEGRATION_KEYS_AFTER_DECRYPTION, $decryptionEvent);

            $configuration->setApiKeys($decryptionEvent->getKeys());

            $this->decryptedIntegrationConfigurations[$integration->getName()] = true;
        }

        return $integration->getIntegrationConfiguration();
    }
}
