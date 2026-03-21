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
                $client->setAccessToken([
                    'refresh_token' => $config['refreshToken'],
                    'access_token'  => '',
                    'token_type'    => 'Bearer',
                    'expires_in'    => 3600,
                    'created'       => 0,
                ]);
                $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderId'] ?? '/');
                $driver  = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            //
        }
    }
}


