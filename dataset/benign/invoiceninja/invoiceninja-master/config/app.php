<?php

use App\Libraries\Utils;

return [

    'name' => env('APP_NAME', 'Invoice Ninja'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', ''),

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', 'SomeRandomStringSomeRandomString'),

    'cipher' => env('APP_CIPHER', 'AES-256-CBC'),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('LOG', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        'Illuminate\Auth\AuthServiceProvider',
        'Collective\Html\HtmlServiceProvider',
        'Illuminate\Bus\BusServiceProvider',
        'Illuminate\Cache\CacheServiceProvider',
        'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
        'Illuminate\Cookie\CookieServiceProvider',
        'Illuminate\Database\DatabaseServiceProvider',
        'Illuminate\Encryption\EncryptionServiceProvider',
        'Illuminate\Filesystem\FilesystemServiceProvider',
        'Illuminate\Foundation\Providers\FoundationServiceProvider',
        'Illuminate\Hashing\HashServiceProvider',
        'Illuminate\Mail\MailServiceProvider',
        'Illuminate\Pagination\PaginationServiceProvider',
        'Illuminate\Pipeline\PipelineServiceProvider',
        'Illuminate\Queue\QueueServiceProvider',
        'Illuminate\Redis\RedisServiceProvider',
        'Illuminate\Auth\Passwords\PasswordResetServiceProvider',
        'Illuminate\Session\SessionServiceProvider',
        'Illuminate\Translation\TranslationServiceProvider',
        'Illuminate\Validation\ValidationServiceProvider',
        'Illuminate\View\ViewServiceProvider',
        'Illuminate\Broadcasting\BroadcastServiceProvider',
        'Illuminate\Notifications\NotificationServiceProvider',

        /*
         * Additional Providers
         */
        'Bootstrapper\BootstrapperL5ServiceProvider',
        'Former\FormerServiceProvider',
        'Barryvdh\Debugbar\ServiceProvider',
        'Intervention\Image\ImageServiceProvider',
        'Webpatser\Countries\CountriesServiceProvider',
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Laravel\Socialite\SocialiteServiceProvider',
        'Jlapp\Swaggervel\SwaggervelServiceProvider',
        'Maatwebsite\Excel\ExcelServiceProvider',
        Websight\GcsProvider\CloudStorageServiceProvider::class,
        'Jaybizzle\LaravelCrawlerDetect\LaravelCrawlerDetectServiceProvider',
        Codedge\Updater\UpdaterServiceProvider::class,
        Nwidart\Modules\LaravelModulesServiceProvider::class,
        Barryvdh\Cors\ServiceProvider::class,
        PragmaRX\Google2FALaravel\ServiceProvider::class,
        'Chumper\Datatable\DatatableServiceProvider',
        Laravel\Tinker\TinkerServiceProvider::class,

        /*
         * Application Service Providers...
         */
        'App\Providers\AuthServiceProvider',
        'App\Providers\AppServiceProvider',
        'App\Providers\ComposerServiceProvider',
        'App\Providers\ConfigServiceProvider',
        'App\Providers\EventServiceProvider',
        'App\Providers\RouteServiceProvider',

        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Davibennun\LaravelPushNotification\LaravelPushNotificationServiceProvider',

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'             => 'Illuminate\Support\Facades\App',
        'Artisan'         => 'Illuminate\Support\Facades\Artisan',
        'Auth'            => 'Illuminate\Support\Facades\Auth',
        'Blade'           => 'Illuminate\Support\Facades\Blade',
        'Cache'           => 'Illuminate\Support\Facades\Cache',
        'ClassLoader'     => 'Illuminate\Support\ClassLoader',
        'Config'          => 'Illuminate\Support\Facades\Config',
        'Controller'      => 'Illuminate\Routing\Controller',
        'Cookie'          => 'Illuminate\Support\Facades\Cookie',
        'Crypt'           => 'Illuminate\Support\Facades\Crypt',
        'DB'              => 'Illuminate\Support\Facades\DB',
        'Eloquent'        => 'Illuminate\Database\Eloquent\Model',
        'Event'           => 'Illuminate\Support\Facades\Event',
        'File'            => 'Illuminate\Support\Facades\File',
        'Gate'            => 'Illuminate\Support\Facades\Gate',
        'Hash'            => 'Illuminate\Support\Facades\Hash',
        'Input'           => 'Illuminate\Support\Facades\Input',
        'Lang'            => 'Illuminate\Support\Facades\Lang',
        'Log'             => 'Illuminate\Support\Facades\Log',
        'Mail'            => 'Illuminate\Support\Facades\Mail',
        'Password'        => 'Illuminate\Support\Facades\Password',
        'Queue'           => 'Illuminate\Support\Facades\Queue',
        'Redirect'        => 'Illuminate\Support\Facades\Redirect',
        'Redis'           => 'Illuminate\Support\Facades\Redis',
        'Request'         => 'Illuminate\Support\Facades\Request',
        'Response'        => 'Illuminate\Support\Facades\Response',
        'Route'           => 'Illuminate\Support\Facades\Route',
        'Schema'          => 'Illuminate\Support\Facades\Schema',
        'Seeder'          => 'Illuminate\Database\Seeder',
        'Session'         => 'Illuminate\Support\Facades\Session',
        'Storage'         => 'Illuminate\Support\Facades\Storage',
        'Str'             => 'Illuminate\Support\Str',
        'URL'             => 'Illuminate\Support\Facades\URL',
        'Validator'       => 'Illuminate\Support\Facades\Validator',
        'View'            => 'Illuminate\Support\Facades\View',

        // Added Class Aliases
        'Form'              => 'Collective\Html\FormFacade',
        'HTML'              => 'Collective\Html\HtmlFacade',
        'SSH'              => 'Illuminate\Support\Facades\SSH',
        'Alert'           => 'Bootstrapper\Facades\Alert',
        'Badge'           => 'Bootstrapper\Facades\Badge',
        'Breadcrumb'      => 'Bootstrapper\Facades\Breadcrumb',
        'Button'          => 'Bootstrapper\Facades\Button',
        'ButtonGroup'     => 'Bootstrapper\Facades\ButtonGroup',
        'ButtonToolbar'   => 'Bootstrapper\Facades\ButtonToolbar',
        'Carousel'        => 'Bootstrapper\Facades\Carousel',
        'DropdownButton'  => 'Bootstrapper\Facades\DropdownButton',
        'Helpers'         => 'Bootstrapper\Facades\Helpers',
        'Icon'            => 'Bootstrapper\Facades\Icon',
        'Label'           => 'Bootstrapper\Facades\Label',
        'MediaObject'     => 'Bootstrapper\Facades\MediaObject',
        'Navbar'          => 'Bootstrapper\Facades\Navbar',
        'Navigation'      => 'Bootstrapper\Facades\Navigation',
        'Paginator'       => 'Bootstrapper\Facades\Paginator',
        'Progress'        => 'Bootstrapper\Facades\Progress',
        'Tabbable'        => 'Bootstrapper\Facades\Tabbable',
        'Table'           => 'Bootstrapper\Facades\Table',
        'Thumbnail'       => 'Bootstrapper\Facades\Thumbnail',
        'Typeahead'       => 'Bootstrapper\Facades\Typeahead',
        'Typography'      => 'Bootstrapper\Facades\Typography',
        'Former'          => 'Former\Facades\Former',
        'Omnipay'         => 'Omnipay\Omnipay',
        'CreditCard'      => 'Omnipay\Common\CreditCard',
        'Image'           => 'Intervention\Image\Facades\Image',
        'Countries'       => 'Webpatser\Countries\CountriesFacade',
        'Carbon'          => 'Carbon\Carbon',
        'Rocketeer'       => 'Rocketeer\Facades\Rocketeer',
        'Socialite'       => 'Laravel\Socialite\Facades\Socialite',
        'Excel'           => 'Maatwebsite\Excel\Facades\Excel',
        'PushNotification' => 'Davibennun\LaravelPushNotification\Facades\PushNotification',
        'Crawler'   => 'Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect',
        'Datatable' => 'Chumper\Datatable\Facades\DatatableFacade',
        'Updater' => Codedge\Updater\UpdaterFacade::class,
        'Module' => Nwidart\Modules\Facades\Module::class,

        'Utils' => App\Libraries\Utils::class,
        'DateUtils' => App\Libraries\DateUtils::class,
        'HTMLUtils' => App\Libraries\HTMLUtils::class,
        'CurlUtils' => App\Libraries\CurlUtils::class,
        'Domain' => App\Constants\Domain::class,
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,

    ],

];
