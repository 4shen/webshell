<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'routes' => [
        'main' => [
            'mautic_core_ajax' => [
                'path'       => '/ajax',
                'controller' => 'MauticCoreBundle:Ajax:delegateAjax',
            ],
            'mautic_core_update' => [
                'path'       => '/update',
                'controller' => 'MauticCoreBundle:Update:index',
            ],
            'mautic_core_update_schema' => [
                'path'       => '/update/schema',
                'controller' => 'MauticCoreBundle:Update:schema',
            ],
            'mautic_core_form_action' => [
                'path'       => '/action/{objectAction}/{objectModel}/{objectId}',
                'controller' => 'MauticCoreBundle:Form:execute',
                'defaults'   => [
                    'objectModel' => '',
                ],
            ],
            'mautic_core_file_action' => [
                'path'       => '/file/{objectAction}/{objectId}',
                'controller' => 'MauticCoreBundle:File:execute',
            ],
            'mautic_themes_index' => [
                'path'       => '/themes',
                'controller' => 'MauticCoreBundle:Theme:index',
            ],
            'mautic_themes_action' => [
                'path'       => '/themes/{objectAction}/{objectId}',
                'controller' => 'MauticCoreBundle:Theme:execute',
            ],
        ],
        'public' => [
            'mautic_js' => [
                'path'       => '/mtc.js',
                'controller' => 'MauticCoreBundle:Js:index',
            ],
            'mautic_base_index' => [
                'path'       => '/',
                'controller' => 'MauticCoreBundle:Default:index',
            ],
            'mautic_secure_root' => [
                'path'       => '/s',
                'controller' => 'MauticCoreBundle:Default:redirectSecureRoot',
            ],
            'mautic_secure_root_slash' => [
                'path'       => '/s/',
                'controller' => 'MauticCoreBundle:Default:redirectSecureRoot',
            ],
            'mautic_remove_trailing_slash' => [
                'path'         => '/{url}',
                'controller'   => 'MauticCoreBundle:Common:removeTrailingSlash',
                'method'       => 'GET',
                'requirements' => [
                    'url' => '.*/$',
                ],
            ],
        ],
        'api' => [
            'mautic_core_api_file_list' => [
                'path'       => '/files/{dir}',
                'controller' => 'MauticCoreBundle:Api\FileApi:list',
            ],
            'mautic_core_api_file_create' => [
                'path'       => '/files/{dir}/new',
                'controller' => 'MauticCoreBundle:Api\FileApi:create',
                'method'     => 'POST',
            ],
            'mautic_core_api_file_delete' => [
                'path'       => '/files/{dir}/{file}/delete',
                'controller' => 'MauticCoreBundle:Api\FileApi:delete',
                'method'     => 'DELETE',
            ],
            'mautic_core_api_theme_list' => [
                'path'       => '/themes',
                'controller' => 'MauticCoreBundle:Api\ThemeApi:list',
            ],
            'mautic_core_api_theme_get' => [
                'path'       => '/themes/{theme}',
                'controller' => 'MauticCoreBundle:Api\ThemeApi:get',
            ],
            'mautic_core_api_theme_create' => [
                'path'       => '/themes/new',
                'controller' => 'MauticCoreBundle:Api\ThemeApi:new',
                'method'     => 'POST',
            ],
            'mautic_core_api_theme_delete' => [
                'path'       => '/themes/{theme}/delete',
                'controller' => 'MauticCoreBundle:Api\ThemeApi:delete',
                'method'     => 'DELETE',
            ],
            'mautic_core_api_stats' => [
                'path'       => '/stats/{table}',
                'controller' => 'MauticCoreBundle:Api\StatsApi:list',
                'defaults'   => [
                    'table' => '',
                ],
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'mautic.core.components' => [
                'id'        => 'mautic_components_root',
                'iconClass' => 'fa-puzzle-piece',
                'priority'  => 60,
            ],
            'mautic.core.channels' => [
                'id'        => 'mautic_channels_root',
                'iconClass' => 'fa-rss',
                'priority'  => 40,
            ],
        ],
        'admin' => [
            'mautic.theme.menu.index' => [
                'route'     => 'mautic_themes_index',
                'iconClass' => 'fa-newspaper-o',
                'id'        => 'mautic_themes_index',
                'access'    => 'core:themes:view',
            ],
        ],
        'extra' => [
            'priority' => -1000,
            'items'    => [
                'name'     => 'extra',
                'children' => [],
            ],
        ],
        'profile' => [
            'priority' => -1000,
            'items'    => [
                'name'     => 'profile',
                'children' => [],
            ],
        ],
    ],
    'services' => [
        'events' => [
            'mautic.core.subscriber' => [
                'class'     => Mautic\CoreBundle\EventListener\CoreSubscriber::class,
                'arguments' => [
                    'mautic.helper.bundle',
                    'mautic.helper.menu',
                    'mautic.helper.user',
                    'templating.helper.assets',
                    'mautic.helper.core_parameters',
                    'security.authorization_checker',
                    'mautic.user.model.user',
                    'event_dispatcher',
                    'translator',
                    'request_stack',
                    'mautic.form.repository.form',
                    'mautic.factory',
                ],
            ],
            'mautic.core.environment.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\EnvironmentSubscriber::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.core.configbundle.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\ConfigSubscriber::class,
                'arguments' => [
                    'mautic.helper.language',
                ],
            ],
            'mautic.core.configbundle.subscriber.theme' => [
                'class'     => \Mautic\CoreBundle\EventListener\ConfigThemeSubscriber::class,
            ],
            'mautic.webpush.js.subscriber' => [
                'class' => \Mautic\CoreBundle\EventListener\BuildJsSubscriber::class,
            ],
            'mautic.core.dashboard.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'mautic.core.model.auditlog',
                    'translator',
                    'router',
                    'mautic.security',
                    'event_dispatcher',
                    'mautic.model.factory',
                ],
            ],

            'mautic.core.maintenance.subscriber' => [
                'class'     => Mautic\CoreBundle\EventListener\MaintenanceSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'mautic.user.token.repository',
                    'translator',
                ],
            ],
            'mautic.core.request.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\RequestSubscriber::class,
                'arguments' => [
                    'security.csrf.token_manager',
                    'translator',
                    'mautic.helper.templating',
                ],
            ],
            'mautic.core.stats.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'mautic.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'mautic.core.assets.subscriber' => [
                'class'     => \Mautic\CoreBundle\EventListener\AssetsSubscriber::class,
                'arguments' => [
                    'templating.helper.assets',
                    'event_dispatcher',
                ],
            ],
            'mautic.core.subscriber.router' => [
                'class'     => \Mautic\CoreBundle\EventListener\RouterSubscriber::class,
                'arguments' => [
                    'router',
                    '%router.request_context.scheme%',
                    '%router.request_context.host%',
                    '%request_listener.https_port%',
                    '%request_listener.http_port%',
                    '%router.request_context.base_url%',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.button_group' => [
                'class' => 'Mautic\CoreBundle\Form\Type\ButtonGroupType',
            ],
            'mautic.form.type.standalone_button' => [
                'class' => 'Mautic\CoreBundle\Form\Type\StandAloneButtonType',
            ],
            'mautic.form.type.form_buttons' => [
                'class' => 'Mautic\CoreBundle\Form\Type\FormButtonsType',
            ],
            'mautic.form.type.sortablelist' => [
                'class' => 'Mautic\CoreBundle\Form\Type\SortableListType',
            ],
            'mautic.form.type.coreconfig' => [
                'class'     => \Mautic\CoreBundle\Form\Type\ConfigType::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.language',
                    'mautic.ip_lookup.factory',
                    '%mautic.ip_lookup_services%',
                    'mautic.ip_lookup',
                ],
            ],
            'mautic.form.type.coreconfig.iplookup_download_data_store_button' => [
                'class'     => \Mautic\CoreBundle\Form\Type\IpLookupDownloadDataStoreButtonType::class,
                'arguments' => [
                    'mautic.helper.template.date',
                    'translator',
                ],
            ],
            'mautic.form.type.theme_list' => [
                'class'     => \Mautic\CoreBundle\Form\Type\ThemeListType::class,
                'arguments' => ['mautic.helper.theme'],
            ],
            'mautic.form.type.daterange' => [
                'class'     => \Mautic\CoreBundle\Form\Type\DateRangeType::class,
                'arguments' => [
                    'session',
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.form.type.slot.saveprefsbutton' => [
                'class'     => 'Mautic\CoreBundle\Form\Type\SlotSavePrefsButtonType',
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.slot.successmessage' => [
                'class'     => Mautic\CoreBundle\Form\Type\SlotSuccessMessageType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.slot.segmentlist' => [
                'class'     => 'Mautic\CoreBundle\Form\Type\SlotSegmentListType',
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.slot.categorylist' => [
                'class'     => \Mautic\CoreBundle\Form\Type\SlotCategoryListType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.slot.preferredchannel' => [
                'class'     => \Mautic\CoreBundle\Form\Type\SlotPreferredChannelType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.slot.channelfrequency' => [
                'class'     => \Mautic\CoreBundle\Form\Type\SlotChannelFrequencyType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.form.type.dynamic_content_filter_entry' => [
                'class'     => \Mautic\CoreBundle\Form\Type\DynamicContentFilterEntryType::class,
                'arguments' => [
                    'mautic.lead.model.list',
                    'mautic.stage.model.stage',
                ],
            ],
            'mautic.form.type.dynamic_content_filter_entry_filters' => [
                'class'     => \Mautic\CoreBundle\Form\Type\DynamicContentFilterEntryFiltersType::class,
                'arguments' => [
                    'translator',
                ],
                'methodCalls' => [
                    'setConnection' => [
                        'database_connection',
                    ],
                ],
            ],
            'mautic.form.type.entity_lookup' => [
                'class'     => \Mautic\CoreBundle\Form\Type\EntityLookupType::class,
                'arguments' => [
                    'mautic.model.factory',
                    'translator',
                    'database_connection',
                    'router',
                ],
            ],
        ],
        'helpers' => [
            'mautic.helper.app_version' => [
                'class' => \Mautic\CoreBundle\Helper\AppVersion::class,
            ],
            'mautic.helper.template.menu' => [
                'class'     => 'Mautic\CoreBundle\Templating\Helper\MenuHelper',
                'arguments' => ['knp_menu.helper'],
                'alias'     => 'menu',
            ],
            'mautic.helper.template.date' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\DateHelper::class,
                'arguments' => [
                    '%mautic.date_format_full%',
                    '%mautic.date_format_short%',
                    '%mautic.date_format_dateonly%',
                    '%mautic.date_format_timeonly%',
                    'translator',
                ],
                'alias' => 'date',
            ],
            'mautic.helper.template.exception' => [
                'class'     => 'Mautic\CoreBundle\Templating\Helper\ExceptionHelper',
                'arguments' => '%kernel.root_dir%',
                'alias'     => 'exception',
            ],
            'mautic.helper.template.gravatar' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\GravatarHelper::class,
                'arguments' => [
                    'mautic.helper.template.default_avatar',
                    'mautic.helper.core_parameters',
                    'request_stack',
                ],
                'alias'     => 'gravatar',
            ],
            'mautic.helper.template.analytics' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\AnalyticsHelper::class,
                'alias'     => 'analytics',
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.helper.template.mautibot' => [
                'class' => 'Mautic\CoreBundle\Templating\Helper\MautibotHelper',
                'alias' => 'mautibot',
            ],
            'mautic.helper.template.canvas' => [
                'class'     => 'Mautic\CoreBundle\Templating\Helper\SidebarCanvasHelper',
                'arguments' => [
                    'event_dispatcher',
                ],
                'alias' => 'canvas',
            ],
            'mautic.helper.template.button' => [
                'class'     => 'Mautic\CoreBundle\Templating\Helper\ButtonHelper',
                'arguments' => [
                    'templating',
                    'translator',
                    'event_dispatcher',
                ],
                'alias' => 'buttons',
            ],
            'mautic.helper.template.content' => [
                'class'     => 'Mautic\CoreBundle\Templating\Helper\ContentHelper',
                'arguments' => [
                    'templating',
                    'event_dispatcher',
                ],
                'alias' => 'content',
            ],
            'mautic.helper.template.formatter' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\FormatterHelper::class,
                'arguments' => [
                    'mautic.helper.template.date',
                    'translator',
                ],
                'alias' => 'formatter',
            ],
            'mautic.helper.template.version' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\VersionHelper::class,
                'arguments' => [
                    'mautic.helper.app_version',
                ],
                'alias' => 'version',
            ],
            'mautic.helper.template.security' => [
                'class'     => \Mautic\CoreBundle\Templating\Helper\SecurityHelper::class,
                'arguments' => [
                    'mautic.security',
                    'request_stack',
                    'event_dispatcher',
                    'security.csrf.token_manager',
                ],
                'alias' => 'security',
            ],
            'mautic.helper.paths' => [
                'class'     => 'Mautic\CoreBundle\Helper\PathsHelper',
                'arguments' => [
                    'mautic.helper.user',
                    'mautic.helper.core_parameters',
                    '%kernel.cache_dir%',
                    '%kernel.logs_dir%',
                    '%kernel.root_dir%',
                ],
            ],
            'mautic.helper.ip_lookup' => [
                'class'     => 'Mautic\CoreBundle\Helper\IpLookupHelper',
                'arguments' => [
                    'request_stack',
                    'doctrine.orm.entity_manager',
                    'mautic.helper.core_parameters',
                    'mautic.ip_lookup',
                ],
            ],
            'mautic.helper.user' => [
                'class'     => 'Mautic\CoreBundle\Helper\UserHelper',
                'arguments' => [
                    'security.token_storage',
                ],
            ],
            'mautic.helper.core_parameters' => [
                'class'     => \Mautic\CoreBundle\Helper\CoreParametersHelper::class,
                'arguments' => [
                    'service_container',
                ],
                'serviceAlias' => 'mautic.config',
            ],
            'mautic.helper.bundle' => [
                'class'     => 'Mautic\CoreBundle\Helper\BundleHelper',
                'arguments' => [
                    '%mautic.bundles%',
                    '%mautic.plugin.bundles%',
                ],
            ],
            'mautic.helper.phone_number' => [
                'class' => 'Mautic\CoreBundle\Helper\PhoneNumberHelper',
            ],
            'mautic.helper.input_helper' => [
                'class' => \Mautic\CoreBundle\Helper\InputHelper::class,
            ],
            'mautic.helper.file_uploader' => [
                'class'     => \Mautic\CoreBundle\Helper\FileUploader::class,
                'arguments' => [
                    'mautic.helper.file_path_resolver',
                ],
            ],
            'mautic.helper.file_path_resolver' => [
                'class'     => \Mautic\CoreBundle\Helper\FilePathResolver::class,
                'arguments' => [
                    'symfony.filesystem',
                    'mautic.helper.input_helper',
                ],
            ],
            'mautic.helper.file_properties' => [
                'class' => \Mautic\CoreBundle\Helper\FileProperties::class,
            ],
            'mautic.helper.trailing_slash' => [
                'class'     => \Mautic\CoreBundle\Helper\TrailingSlashHelper::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.helper.token_builder' => [
                'class'     => \Mautic\CoreBundle\Helper\BuilderTokenHelper::class,
                'arguments' => [
                    'mautic.security',
                    'mautic.model.factory',
                    'database_connection',
                    'mautic.helper.user',
                ],
            ],
            'mautic.helper.token_builder.factory' => [
                'class'     => \Mautic\CoreBundle\Helper\BuilderTokenHelperFactory::class,
                'arguments' => [
                    'mautic.security',
                    'mautic.model.factory',
                    'database_connection',
                    'mautic.helper.user',
                ],
            ],
        ],
        'menus' => [
            'mautic.menu.main' => [
                'alias' => 'main',
            ],
            'mautic.menu.admin' => [
                'alias'   => 'admin',
                'options' => [
                    'template' => 'MauticCoreBundle:Menu:admin.html.php',
                ],
            ],
            'mautic.menu.extra' => [
                'alias'   => 'extra',
                'options' => [
                    'template' => 'MauticCoreBundle:Menu:extra.html.php',
                ],
            ],
            'mautic.menu.profile' => [
                'alias'   => 'profile',
                'options' => [
                    'template' => 'MauticCoreBundle:Menu:profile_inline.html.php',
                ],
            ],
        ],
        'commands' => [
            'mautic.core.command.transifex_pull' => [
                'tag'       => 'console.command',
                'class'     => \Mautic\CoreBundle\Command\PullTransifexCommand::class,
                'arguments' => [
                    'transifex.factory',
                    'translator',
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.core.command.transifex_push' => [
                'tag'       => 'console.command',
                'class'     => \Mautic\CoreBundle\Command\PushTransifexCommand::class,
                'arguments' => [
                    'transifex.factory',
                    'translator',
                ],
            ],
            'mautic.core.command.apply_update' => [
                'tag'       => 'console.command',
                'class'     => \Mautic\CoreBundle\Command\ApplyUpdatesCommand::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.core_parameters',
                    'mautic.update.step_provider',
                ],
            ],
        ],
        'other' => [
            'mautic.cache.warmer.middleware' => [
                'class'     => \Mautic\CoreBundle\Cache\MiddlewareCacheWarmer::class,
                'tag'       => 'kernel.cache_warmer',
                'arguments' => [
                    '%kernel.environment%',
                ],
            ],
            'mautic.http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
            'mautic.http.client.psr-18' => [
                // Warning: Only dev dependency (for TransifexFactory)
                // Can be replaced with 'mautic.http.client' once the standard Guzzle
                // Client implements \Psr\Http\Client\ClientInterface
                // @see https://github.com/guzzle/guzzle/pull/2525
                // When removing, remove also the ricardofiorani/guzzle-psr18-adapter dependency.
                'class' => \RicardoFiorani\GuzzlePsr18Adapter\Client::class,
            ],
            'symfony.filesystem' => [
                'class' => \Symfony\Component\Filesystem\Filesystem::class,
            ],

            // Error handler
            'mautic.core.errorhandler.subscriber' => [
                'class'     => 'Mautic\CoreBundle\EventListener\ErrorHandlingListener',
                'arguments' => [
                    'monolog.logger.mautic',
                    'monolog.logger',
                    "@=container.has('monolog.logger.chrome') ? container.get('monolog.logger.chrome') : null",
                ],
                'tag' => 'kernel.event_subscriber',
            ],

            // Configurator (used in installer and managing global config]
            'mautic.configurator' => [
                'class'     => 'Mautic\CoreBundle\Configurator\Configurator',
                'arguments' => [
                    'mautic.helper.paths',
                ],
            ],

            // System uses
            'mautic.di.env_processor.nullable' => [
                'class' => \Mautic\CoreBundle\DependencyInjection\EnvProcessor\NullableProcessor::class,
                'tag'   => 'container.env_var_processor',
            ],
            'mautic.di.env_processor.int_nullable' => [
                'class' => \Mautic\CoreBundle\DependencyInjection\EnvProcessor\IntNullableProcessor::class,
                'tag'   => 'container.env_var_processor',
            ],
            'mautic.cipher.openssl' => [
                'class'     => \Mautic\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher::class,
                'arguments' => ['%kernel.environment%'],
            ],
            'mautic.factory' => [
                'class'     => 'Mautic\CoreBundle\Factory\MauticFactory',
                'arguments' => 'service_container',
            ],
            'mautic.model.factory' => [
                'class'     => 'Mautic\CoreBundle\Factory\ModelFactory',
                'arguments' => 'service_container',
            ],
            'mautic.templating.name_parser' => [
                'class'     => 'Mautic\CoreBundle\Templating\TemplateNameParser',
                'arguments' => 'kernel',
            ],
            'mautic.route_loader' => [
                'class'     => 'Mautic\CoreBundle\Loader\RouteLoader',
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.core_parameters',
                ],
                'tag' => 'routing.loader',
            ],
            'mautic.security' => [
                'class'     => 'Mautic\CoreBundle\Security\Permissions\CorePermissions',
                'arguments' => [
                    'mautic.helper.user',
                    'translator',
                    'mautic.helper.core_parameters',
                    '%mautic.bundles%',
                    '%mautic.plugin.bundles%',
                ],
            ],
            'mautic.translation.loader' => [
                'class'     => \Mautic\CoreBundle\Loader\TranslationLoader::class,
                'arguments' => [
                    'mautic.helper.bundle',
                    'mautic.helper.paths',
                ],
                'tag'       => 'translation.loader',
                'alias'     => 'mautic',
            ],
            'mautic.tblprefix_subscriber' => [
                'class'     => 'Mautic\CoreBundle\EventListener\DoctrineEventsSubscriber',
                'tag'       => 'doctrine.event_subscriber',
                'arguments' => '%mautic.db_table_prefix%',
            ],
            'mautic.exception.listener' => [
                'class'     => 'Mautic\CoreBundle\EventListener\ExceptionListener',
                'arguments' => [
                    'router',
                    '"MauticCoreBundle:Exception:show"',
                    'monolog.logger.mautic',
                ],
                'tag'          => 'kernel.event_listener',
                'tagArguments' => [
                    'event'    => 'kernel.exception',
                    'method'   => 'onKernelException',
                    'priority' => 255,
                ],
            ],
            'transifex.factory' => [
                'class'     => \Mautic\CoreBundle\Factory\TransifexFactory::class,
                'arguments' => [
                    'mautic.http.client.psr-18',
                    'mautic.helper.core_parameters',
                ],
            ],
            // Helpers
            'mautic.helper.assetgeneration' => [
                'class'     => \Mautic\CoreBundle\Helper\AssetGenerationHelper::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.bundle',
                    'mautic.helper.paths',
                    'mautic.helper.app_version',
                ],
            ],
            'mautic.helper.cookie' => [
                'class'     => 'Mautic\CoreBundle\Helper\CookieHelper',
                'arguments' => [
                    '%mautic.cookie_path%',
                    '%mautic.cookie_domain%',
                    '%mautic.cookie_secure%',
                    '%mautic.cookie_httponly%',
                    'request_stack',
                ],
            ],
            'mautic.helper.cache_storage' => [
                'class'     => Mautic\CoreBundle\Helper\CacheStorageHelper::class,
                'arguments' => [
                    '"db"',
                    '%mautic.db_table_prefix%',
                    'doctrine.dbal.default_connection',
                    '%kernel.cache_dir%',
                ],
            ],
            'mautic.helper.update' => [
                'class'     => \Mautic\CoreBundle\Helper\UpdateHelper::class,
                'arguments' => [
                    'mautic.helper.paths',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                    'mautic.http.client',
                    'mautic.helper.update.release_parser',
                ],
            ],
            'mautic.helper.update.release_parser' => [
                'class'     => \Mautic\CoreBundle\Helper\Update\Github\ReleaseParser::class,
                'arguments' => [
                    'mautic.http.client',
                ],
            ],
            'mautic.helper.cache' => [
                'class'     => \Mautic\CoreBundle\Helper\CacheHelper::class,
                'arguments' => [
                    '%kernel.cache_dir%',
                    'session',
                    'mautic.helper.paths',
                ],
            ],
            'mautic.helper.templating' => [
                'class'     => 'Mautic\CoreBundle\Helper\TemplatingHelper',
                'arguments' => [
                    'kernel',
                ],
            ],
            'mautic.helper.theme' => [
                'class'     => 'Mautic\CoreBundle\Helper\ThemeHelper',
                'arguments' => [
                    'mautic.helper.paths',
                    'mautic.helper.templating',
                    'translator',
                    'mautic.helper.core_parameters',
                ],
                'methodCalls' => [
                    'setDefaultTheme' => [
                        '%mautic.theme%',
                    ],
                ],
            ],
            'mautic.helper.encryption' => [
                'class'     => \Mautic\CoreBundle\Helper\EncryptionHelper::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.cipher.openssl',
                ],
            ],
            'mautic.helper.language' => [
                'class'     => 'Mautic\CoreBundle\Helper\LanguageHelper',
                'arguments' => [
                    'mautic.helper.paths',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                    'mautic.http.connector',
                ],
            ],
            'mautic.helper.url' => [
                'class'     => \Mautic\CoreBundle\Helper\UrlHelper::class,
                'arguments' => [
                    'mautic.http.connector',
                    '%mautic.link_shortener_url%',
                    'monolog.logger.mautic',
                ],
            ],
            // Menu
            'mautic.helper.menu' => [
                'class'     => 'Mautic\CoreBundle\Menu\MenuHelper',
                'arguments' => [
                    'mautic.security',
                    'request_stack',
                    'mautic.helper.core_parameters',
                    'mautic.helper.integration',
                ],
            ],
            'mautic.helper.hash' => [
                'class' => \Mautic\CoreBundle\Helper\HashHelper\HashHelper::class,
            ],
            'mautic.helper.random' => [
                'class' => \Mautic\CoreBundle\Helper\RandomHelper\RandomHelper::class,
            ],
            'mautic.menu_renderer' => [
                'class'     => \Mautic\CoreBundle\Menu\MenuRenderer::class,
                'arguments' => [
                    'knp_menu.matcher',
                    'mautic.helper.templating',
                ],
                'tag'   => 'knp_menu.renderer',
                'alias' => 'mautic',
            ],
            'mautic.menu.builder' => [
                'class'     => 'Mautic\CoreBundle\Menu\MenuBuilder',
                'arguments' => [
                    'knp_menu.factory',
                    'knp_menu.matcher',
                    'event_dispatcher',
                    'mautic.helper.menu',
                ],
            ],
            // IP Lookup
            'mautic.ip_lookup.factory' => [
                'class'     => 'Mautic\CoreBundle\Factory\IpLookupFactory',
                'arguments' => [
                    '%mautic.ip_lookup_services%',
                    'monolog.logger.mautic',
                    'mautic.http.connector',
                    '%kernel.cache_dir%',
                ],
            ],
            'mautic.ip_lookup' => [
                'class'     => 'Mautic\CoreBundle\IpLookup\AbstractLookup', // bogus just to make cache compilation happy
                'factory'   => ['@mautic.ip_lookup.factory', 'getService'],
                'arguments' => [
                    '%mautic.ip_lookup_service%',
                    '%mautic.ip_lookup_auth%',
                    '%mautic.ip_lookup_config%',
                    'mautic.http.connector',
                ],
            ],
            // Other
            'mautic.http.connector' => [
                'class'   => 'Joomla\Http\Http',
                'factory' => ['Joomla\Http\HttpFactory', 'getHttp'],
            ],

            'twig.controller.exception.class' => 'Mautic\CoreBundle\Controller\ExceptionController',

            // Form extensions
            'mautic.form.extension.custom' => [
                'class'        => \Mautic\CoreBundle\Form\Extension\CustomFormExtension::class,
                'arguments'    => [
                    'event_dispatcher',
                ],
                'tag'          => 'form.type_extension',
                'tagArguments' => [
                    'extended_type' => Symfony\Component\Form\Extension\Core\Type\FormType::class,
                ],
            ],

            // Twig
            'templating.twig.extension.slot' => [
                'class'     => \Mautic\CoreBundle\Templating\Twig\Extension\SlotExtension::class,
                'arguments' => [
                    'templating.helper.slots',
                ],
                'tag' => 'twig.extension',
            ],
            'templating.twig.extension.asset' => [
                'class'     => 'Mautic\CoreBundle\Templating\Twig\Extension\AssetExtension',
                'arguments' => [
                    'templating.helper.assets',
                ],
                'tag' => 'twig.extension',
            ],
            // Schema
            'mautic.schema.helper.column' => [
                'class'     => 'Mautic\CoreBundle\Doctrine\Helper\ColumnSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%mautic.db_table_prefix%',
                ],
            ],
            'mautic.schema.helper.index' => [
                'class'     => 'Mautic\CoreBundle\Doctrine\Helper\IndexSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%mautic.db_table_prefix%',
                ],
            ],
            'mautic.schema.helper.table' => [
                'class'     => 'Mautic\CoreBundle\Doctrine\Helper\TableSchemaHelper',
                'arguments' => [
                    'database_connection',
                    '%mautic.db_table_prefix%',
                    'mautic.schema.helper.column',
                ],
            ],
            'mautic.form.list.validator.circular' => [
                'class'     => Mautic\CoreBundle\Form\Validator\Constraints\CircularDependencyValidator::class,
                'arguments' => [
                    'mautic.lead.model.list',
                    'request_stack',
                ],
                'tag' => 'validator.constraint_validator',
            ],
            // Logger
            'mautic.monolog.handler' => [
                'class'     => \Mautic\CoreBundle\Monolog\Handler\FileLogHandler::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.monolog.fulltrace.formatter',
                ],
            ],

            // Update steps
            'mautic.update.step_provider' => [
                'class' => \Mautic\CoreBundle\Update\StepProvider::class,
            ],
            'mautic.update.step.delete_cache' => [
                'class'     => \Mautic\CoreBundle\Update\Step\DeleteCacheStep::class,
                'arguments' => [
                    'mautic.helper.cache',
                    'translator',
                ],
                'tag' => 'mautic.update_step',
            ],
            'mautic.update.step.finalize' => [
                'class'     => \Mautic\CoreBundle\Update\Step\FinalizeUpdateStep::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.paths',
                    'session',
                    'mautic.helper.app_version',
                ],
                'tag' => 'mautic.update_step',
            ],
            'mautic.update.step.install_new_files' => [
                'class'     => \Mautic\CoreBundle\Update\Step\InstallNewFilesStep::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.update',
                    'mautic.helper.paths',
                ],
                'tag' => 'mautic.update_step',
            ],
            'mautic.update.step.remove_deleted_files' => [
                'class'     => \Mautic\CoreBundle\Update\Step\RemoveDeletedFilesStep::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.paths',
                    'monolog.logger.mautic',
                ],
                'tag' => 'mautic.update_step',
            ],
            'mautic.update.step.update_schema' => [
                'class'     => \Mautic\CoreBundle\Update\Step\UpdateSchemaStep::class,
                'arguments' => [
                    'translator',
                    'service_container',
                ],
                'tag' => 'mautic.update_step',
            ],
            'mautic.update.step.update_translations' => [
                'class'     => \Mautic\CoreBundle\Update\Step\UpdateTranslationsStep::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.language',
                    'monolog.logger.mautic',
                ],
                'tag' => 'mautic.update_step',
            ],
        ],
        'models' => [
            'mautic.core.model.auditlog' => [
                'class' => 'Mautic\CoreBundle\Model\AuditLogModel',
            ],
            'mautic.core.model.notification' => [
                'class'     => 'Mautic\CoreBundle\Model\NotificationModel',
                'arguments' => [
                    'mautic.helper.paths',
                    'mautic.helper.update',
                    'debril.reader',
                    'mautic.helper.core_parameters',
                ],
                'methodCalls' => [
                    'setDisableUpdates' => [
                        '%mautic.security.disableUpdates%',
                    ],
                ],
            ],
            'mautic.core.model.form' => [
                'class' => 'Mautic\CoreBundle\Model\FormModel',
            ],
        ],
        'validator' => [
            'mautic.core.validator.file_upload' => [
                'class'     => \Mautic\CoreBundle\Validator\FileUploadValidator::class,
                'arguments' => [
                    'translator',
                ],
            ],
        ],
    ],

    'ip_lookup_services' => [
        'extreme-ip' => [
            'display_name' => 'Extreme-IP',
            'class'        => 'Mautic\CoreBundle\IpLookup\ExtremeIpLookup',
        ],
        'freegeoip' => [
            'display_name' => 'Ipstack.com',
            'class'        => 'Mautic\CoreBundle\IpLookup\IpstackLookup',
        ],
        'geobytes' => [
            'display_name' => 'Geobytes',
            'class'        => 'Mautic\CoreBundle\IpLookup\GeobytesLookup',
        ],
        'geoips' => [
            'display_name' => 'GeoIPs',
            'class'        => 'Mautic\CoreBundle\IpLookup\GeoipsLookup',
        ],
        'ipinfodb' => [
            'display_name' => 'IPInfoDB',
            'class'        => 'Mautic\CoreBundle\IpLookup\IpinfodbLookup',
        ],
        'maxmind_country' => [
            'display_name' => 'MaxMind - Country Geolocation',
            'class'        => 'Mautic\CoreBundle\IpLookup\MaxmindCountryLookup',
        ],
        'maxmind_omni' => [
            'display_name' => 'MaxMind - Insights (formerly Omni]',
            'class'        => 'Mautic\CoreBundle\IpLookup\MaxmindOmniLookup',
        ],
        'maxmind_precision' => [
            'display_name' => 'MaxMind - GeoIP2 Precision',
            'class'        => 'Mautic\CoreBundle\IpLookup\MaxmindPrecisionLookup',
        ],
        'maxmind_download' => [
            'display_name' => 'MaxMind - GeoLite2 City Download',
            'class'        => 'Mautic\CoreBundle\IpLookup\MaxmindDownloadLookup',
        ],
        'telize' => [
            'display_name' => 'Telize',
            'class'        => 'Mautic\CoreBundle\IpLookup\TelizeLookup',
        ],
        'ip2loctionlocal' => [
            'display_name' => 'IP2Location Local Bin File',
            'class'        => 'Mautic\CoreBundle\IpLookup\IP2LocationBinLookup',
        ],
        'ip2loctionapi' => [
            'display_name' => 'IP2Location Web Service',
            'class'        => 'Mautic\CoreBundle\IpLookup\IP2LocationAPILookup',
        ],
    ],

    'parameters' => [
        'site_url'                        => '',
        'webroot'                         => '',
        'cache_path'                      => '%kernel.root_dir%/../var/cache',
        'log_path'                        => '%kernel.root_dir%/../var/logs',
        'max_log_files'                   => 7,
        'log_file_name'                   => 'mautic_%kernel.environment%.php',
        'image_path'                      => 'media/images',
        'tmp_path'                        => '%kernel.root_dir%/../var/tmp',
        'theme'                           => 'blank',
        'theme_import_allowed_extensions' => ['json', 'twig', 'css', 'js', 'htm', 'html', 'txt', 'jpg', 'jpeg', 'png', 'gif'],
        'db_driver'                       => 'pdo_mysql',
        'db_host'                         => '127.0.0.1',
        'db_port'                         => 3306,
        'db_name'                         => '',
        'db_user'                         => '',
        'db_password'                     => '',
        'db_table_prefix'                 => '',
        'db_server_version'               => '5.5',
        'locale'                          => 'en_US',
        'secret_key'                      => '',
        'dev_hosts'                       => [],
        'trusted_hosts'                   => [],
        'trusted_proxies'                 => [],
        'rememberme_key'                  => hash('sha1', uniqid(mt_rand())),
        'rememberme_lifetime'             => 31536000, //365 days in seconds
        'rememberme_path'                 => '/',
        'rememberme_domain'               => '',
        'default_pagelimit'               => 30,
        'default_timezone'                => 'UTC',
        'date_format_full'                => 'F j, Y g:i a T',
        'date_format_short'               => 'D, M d',
        'date_format_dateonly'            => 'F j, Y',
        'date_format_timeonly'            => 'g:i a',
        'ip_lookup_service'               => 'maxmind_download',
        'ip_lookup_auth'                  => '',
        'ip_lookup_config'                => [],
        'ip_lookup_create_organization'   => false,
        'transifex_username'              => '',
        'transifex_password'              => '',
        'update_stability'                => 'stable',
        'cookie_path'                     => '/',
        'cookie_domain'                   => '',
        'cookie_secure'                   => null,
        'cookie_httponly'                 => false,
        'do_not_track_ips'                => [],
        'do_not_track_bots'               => [
            'MSNBOT',
            'msnbot-media',
            'bingbot',
            'Googlebot',
            'Google Web Preview',
            'Mediapartners-Google',
            'Baiduspider',
            'Ezooms',
            'YahooSeeker',
            'Slurp',
            'AltaVista',
            'AVSearch',
            'Mercator',
            'Scooter',
            'InfoSeek',
            'Ultraseek',
            'Lycos',
            'Wget',
            'YandexBot',
            'Java/1.4.1_04',
            'SiteBot',
            'Exabot',
            'AhrefsBot',
            'MJ12bot',
            'NetSeer crawler',
            'TurnitinBot',
            'magpie-crawler',
            'Nutch Crawler',
            'CMS Crawler',
            'rogerbot',
            'Domnutch',
            'ssearch_bot',
            'XoviBot',
            'digincore',
            'fr-crawler',
            'SeznamBot',
            'Seznam screenshot-generator',
            'Facebot',
            'facebookexternalhit',
        ],
        'do_not_track_internal_ips' => [],
        'track_private_ip_ranges'   => false,
        'link_shortener_url'        => null,
        'cached_data_timeout'       => 10,
        'batch_sleep_time'          => 1,
        'batch_campaign_sleep_time' => false,
        'cors_restrict_domains'     => true,
        'cors_valid_domains'        => [],
        'max_entity_lock_time'      => 0,
        'default_daterange_filter'  => '-1 month',
        'debug'                     => false,
        'rss_notification_url'      => '',
        'translations_list_url'     => 'https://language-packs.mautic.com/manifest.json',
        'translations_fetch_url'    => 'https://language-packs.mautic.com/',
        'stats_update_url'          => 'https://updates.mautic.org/stats/send', // set to empty in config file to disable
        'install_source'            => 'Mautic',
        'system_update_url'         => 'https://api.github.com/repos/mautic/mautic/releases',
    ],
];
