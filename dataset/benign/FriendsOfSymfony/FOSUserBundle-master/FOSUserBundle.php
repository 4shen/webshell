<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use FOS\UserBundle\DependencyInjection\Compiler\CheckForMailerPass;
use FOS\UserBundle\DependencyInjection\Compiler\CheckForSessionPass;
use FOS\UserBundle\DependencyInjection\Compiler\InjectRememberMeServicesPass;
use FOS\UserBundle\DependencyInjection\Compiler\InjectUserCheckerPass;
use FOS\UserBundle\DependencyInjection\Compiler\ValidationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Matthieu Bontemps <matthieu@knplabs.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class FOSUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ValidationPass());
        $container->addCompilerPass(new InjectUserCheckerPass());
        $container->addCompilerPass(new InjectRememberMeServicesPass());
        $container->addCompilerPass(new CheckForSessionPass());
        $container->addCompilerPass(new CheckForMailerPass());

        $this->addRegisterMappingsPass($container);
    }

    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $mappings = [
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'FOS\UserBundle\Model',
        ];

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['fos_user.model_manager_name'], 'fos_user.backend_type_orm'));
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, ['fos_user.model_manager_name'], 'fos_user.backend_type_mongodb'));
        }

        if (class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createXmlMappingDriver($mappings, ['fos_user.model_manager_name'], 'fos_user.backend_type_couchdb'));
        }
    }
}
