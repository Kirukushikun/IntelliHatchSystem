<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    //    if(env('APP_ENV') !== 'local'){
    //        URL::forceScheme('https');
    //    }

        try {
            Storage::extend('google', function ($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->addScope(\Google\Service\Drive::DRIVE);
                $token = $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);
                $client->setAccessToken($token);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderPath'] ?? '/');
                $driver  = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            //
        }
    }
}


