<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerQuote\Plugin\LoginAsCustomerApi;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;

/**
 * Remove all items from guest shopping cart before execute. Mark customer cart as not-guest after execute
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProcessShoppingCartPlugin
{
    /**
     * @var GetAuthenticationDataBySecretInterface
     */
    private $getAuthenticationDataBySecret;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->getAuthenticationDataBySecret = $getAuthenticationDataBySecret;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Remove all items from guest shopping cart
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @param string $secret
     * @return null
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        AuthenticateCustomerBySecretInterface $subject,
        string $secret
    ) {
        if (!$this->customerSession->getId()) {
            $quote = $this->checkoutSession->getQuote();
            /* Remove items from guest cart */
            $quote->removeAllItems();
            $this->quoteRepository->save($quote);
        }
        return null;
    }

    /**
     * Mark customer cart as not-guest
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @param void $result
     * @param string $secret
     * @return void
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthenticateCustomerBySecretInterface $subject,
        $result,
        string $secret
    ) {
        $this->checkoutSession->loadCustomerQuote();
        $quote = $this->checkoutSession->getQuote();

        $quote->setCustomerIsGuest(0);
        $this->quoteRepository->save($quote);
    }
}
