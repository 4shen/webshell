<?php

namespace Webkul\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate and seed command, publish assets and config, link storage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Install and configure bagisto
     */
    public function handle()
    {
        $this->checkForEnvFile();

        // running `php artisan migrate`
        $this->warn('Step: Migrating all tables into database...');
        $migrate = shell_exec('php artisan migrate:fresh');
        $this->info($migrate);

        // running `php artisan db:seed`
        $this->warn('Step: seeding basic data for bagisto kickstart...');
        $result = shell_exec('php artisan db:seed');
        $this->info($result);

        // running `php artisan vendor:publish --all`
        $this->warn('Step: Publishing Assets and Configurations...');
        $result = shell_exec('php artisan vendor:publish --all');
        $this->info($result);

        // running `php artisan storage:link`
        $this->warn('Step: Linking Storage directory...');
        $result = shell_exec('php artisan storage:link');
        $this->info($result);

        // running `composer dump-autoload`
        $this->warn('Step: Composer Autoload...');
        $result = shell_exec('composer dump-autoload');
        $this->info($result);

        $this->info('-----------------------------');
        $this->info('Now, run `php artisan serve` to start using Bagisto');
        $this->info('Cheers!');
    }

    /**
    *  Checking .env file and if not found then create .env file.
    *  Then ask for database name, password & username to set
    *  On .env file so that we can easily migrate to our db
    */
    public function checkForEnvFile()
    {
        $envExists = File::exists(base_path() . '/.env');
        if (! $envExists) {
            $this->info('Creating the environment configuration file.');
            $this->createEnvFile();
        } else {
            $this->info('Great! your environment configuration file aready exists.');
        }
    }

    /**
     * Create a new .env file.
     */
    public function createEnvFile()
    {
        try {
            File::copy('.env.example', '.env');
            Artisan::call('key:generate');
            $this->envUpdate('APP_URL=', 'http://localhost:8000');

            $locale = $this->choice('Please select the default locale or press enter to continue', ['ar', 'en', 'fa', 'nl', 'pt_BR'], 1);
            $this->envUpdate('APP_LOCALE=', $locale);
    
            $TimeZones = timezone_identifiers_list();
            $timezone = $this->anticipate('Please enter the default timezone', $TimeZones, date_default_timezone_get());
            $this->envUpdate('APP_TIMEZONE=', $timezone);

            $currency = $this->choice('Please enter the default currency', ['USD', 'EUR'], 'USD');
            $this->envUpdate('APP_CURRENCY=', $currency);


            $this->addDatabaseDetails();
        } catch (\Exception $e) {
            $this->error('Error in creating .env file, please create it manually and then run `php artisan migrate` again.');
        }
    }

    /**
     * Add the database credentials to the .env file.
     */
    public function addDatabaseDetails()
    {
        $dbName = $this->ask('What is the database name to be used by bagisto?');
        $dbUser = $this->anticipate('What is your database username?', ['root']);
        $dbPass = $this->secret('What is your database password?');

        $this->envUpdate('DB_DATABASE=', $dbName);
        $this->envUpdate('DB_USERNAME=', $dbUser);
        $this->envUpdate('DB_PASSWORD=', $dbPass);
    }

    /**
     * Update the .env values.
     */
    public static function envUpdate($key, $value)
    {
        $path = base_path() . '/.env';
        $data = file($path);
        $keyValueData = $changedData = [];
         
        if ($data) {
            foreach ($data as $line) {
                $line = preg_replace('/\s+/', '', $line);
                $rowValues = explode('=', $line);

                if (strlen($line) !== 0) {
                    $keyValueData[$rowValues[0]] = $rowValues[1];

                    if (strpos($key, $rowValues[0]) !== false) {
                        $keyValueData[$rowValues[0]] = $value;
                    }
                }               
            }
        }

        foreach ($keyValueData as $key => $value) {
            $changedData[] = $key . '=' . $value;
        }

        $changedData = implode(PHP_EOL, $changedData);

        file_put_contents($path, $changedData);
    }
}
