<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ApiBundle\EventListener;

use Mautic\ApiBundle\Helper\RequestHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

class ApiSubscriber implements EventSubscriberInterface
{
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        TranslatorInterface $translator
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->translator           = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 255],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    /**
     * Check for API requests and throw denied access if API is disabled.
     *
     * @throws AccessDeniedHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Ignore if not an API request
        if (!RequestHelper::isApiRequest($request)) {
            return;
        }

        // Prevent access to API if disabled
        $apiEnabled = $this->coreParametersHelper->get('api_enabled');
        if (!$apiEnabled) {
            $response   = new JsonResponse(
                [
                    'errors' => [
                        [
                            'message' => $this->translator->trans('mautic.api.error.api.disabled'),
                            'code'    => 403,
                            'type'    => 'api_disabled',
                        ],
                    ],
                ],
                403
            );

            $event->setResponse($response);

            return;
        }

        // Prevent access via basic auth if it is disabled
        $hasBasicAuth     = RequestHelper::hasBasicAuth($request);
        $basicAuthEnabled = $this->coreParametersHelper->get('api_enable_basic_auth');

        if ($hasBasicAuth && !$basicAuthEnabled) {
            $response   = new JsonResponse(
                [
                    'errors' => [
                        [
                            'message' => $this->translator->trans('mautic.api.error.basic.auth.disabled'),
                            'code'    => 403,
                            'type'    => 'access_denied',
                        ],
                    ],
                ],
                403
            );

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request      = $event->getRequest();
        $isApiRequest = RequestHelper::isApiRequest($request);
        $hasBasicAuth = RequestHelper::hasBasicAuth($event->getRequest());

        // Ignore if this is not an API request
        if (!$isApiRequest) {
            return;
        }

        // Ignore if this does not contain an error response
        $response = $event->getResponse();
        $content  = $response->getContent();
        if (false === strpos($content, 'error')) {
            return;
        }

        // Ignore if content is not json
        if (!$data = json_decode($content, true)) {
            return;
        }

        // Ignore if an error was not found in the JSON response
        if (!isset($data['error'])) {
            return;
        }

        // Override api messages with something useful
        $type  = null;
        $error = $data['error'];
        if (is_array($error)) {
            if (!isset($error['message'])) {
                return;
            }

            // Catch useless oauth1a errors
            $error = $error['message'];
        }

        switch ($error) {
            case 'access_denied':
                $type    = $error;
                $message = $this->translator->trans('mautic.api.auth.error.accessdenied');

                if ($hasBasicAuth) {
                    if ($this->coreParametersHelper->get('api_enable_basic_auth')) {
                        $message = $this->translator->trans('mautic.api.error.basic.auth.invalid.credentials');
                    } else {
                        $message = $this->translator->trans('mautic.api.error.basic.auth.disabled');
                    }
                }

                break;
            default:
                if (isset($data['error_description'])) {
                    $message = $data['error_description'];
                    $type    = $error;
                } elseif ($this->translator->hasId('mautic.api.auth.error.'.$error)) {
                    $message = $this->translator->trans('mautic.api.auth.error.'.$error);
                    $type    = $error;
                }
        }

        // Message was not overriden so leave as is
        if (!isset($message)) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $response   = new JsonResponse(
            [
                'errors' => [
                    [
                        'message' => $message,
                        'code'    => $response->getStatusCode(),
                        'type'    => $type,
                    ],
                ],
            ],
            $statusCode
        );

        $event->setResponse($response);
    }
}
