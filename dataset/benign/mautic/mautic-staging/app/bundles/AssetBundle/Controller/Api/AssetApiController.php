<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\AssetBundle\Controller\Api;

use Mautic\ApiBundle\Controller\CommonApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class AssetApiController.
 */
class AssetApiController extends CommonApiController
{
    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('asset');
        $this->entityClass      = 'Mautic\AssetBundle\Entity\Asset';
        $this->entityNameOne    = 'asset';
        $this->entityNameMulti  = 'assets';
        $this->serializerGroups = ['assetDetails', 'categoryList', 'publishDetails'];

        parent::initialize($event);
    }

    /**
     * Gives child controllers opportunity to analyze and do whatever to an entity before going through serializer.
     *
     * @param        $entity
     * @param string $action
     *
     * @return mixed
     */
    protected function preSerializeEntity(&$entity, $action = 'view')
    {
        $entity->setDownloadUrl(
            $this->model->generateUrl($entity, true)
        );
    }

    /**
     * Convert posted parameters into what the form needs in order to successfully bind.
     *
     * @param $parameters
     * @param $entity
     * @param $action
     *
     * @return mixed
     */
    protected function prepareParametersForBinding($parameters, $entity, $action)
    {
        $assetDir = $this->get('mautic.helper.core_parameters')->get('upload_dir');
        $entity->setUploadDir($assetDir);

        if (isset($parameters['file'])) {
            if ('local' === $parameters['storageLocation']) {
                $entity->setPath($parameters['file']);
                $entity->setFileInfoFromFile();

                if (null === $entity->loadFile()) {
                    return $this->returnError('File '.$parameters['file'].' was not found in the asset directory.', Response::HTTP_BAD_REQUEST);
                }
            } elseif ('remote' === $parameters['storageLocation']) {
                $parameters['remotePath'] = $parameters['file'];
                $entity->setFileInfoFromFile();
                $entity->setFileNameFromRemote();
            }

            unset($parameters['file']);
        } elseif ('new' === $action) {
            return $this->returnError('File of the asset is required.', Response::HTTP_BAD_REQUEST);
        }

        return $parameters;
    }
}
