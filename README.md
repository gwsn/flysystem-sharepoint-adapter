# Flysystem adapter for the Sharepoint Graph API

This package contains a adapter for [Flysystem](https://flysystem.thephpleague.com/) to use Sharepoint as filestorage. 


## Installation

You can install the package via composer:

``` bash
composer require gwsn/flysystem-sharepoint-adapter
```


## Usage

You need to request a new clientId and clientSecret for a new application on Azure.

1. Go to `Azure portal` https://portal.azure.com
2. Go to `Active Directory`
3. Go to `App registrations`
4. Click on `new Registration` and follow the wizard.  
  (give it a name like mine is 'gwsn-sharepoint-connector' and make a decision on the supported accounts, single tenant should be enough but this depends on your organisation)
5. When created the application is created write down the following details 
6. 'Application (client) id', this will be your `$clientId`
7. 'Directory (tenant) id', this will be your `$tenantId`
8. Then we go in the menu to the `API permissions` to set the permissions that are required
9. The click on `Add a permission` and add the following permissions:  
  Microsoft Graph:
    - Files.ReadWrite.All
    - Sites.ReadWrite.All
    - User.Read
10. Click on the `Grant admin consent for ...Company...`
11. Go in the menu to `Certificates & secrets`
12. Click on `new client secret`
13. Give it a description and expiry date and the value will be your `$clientSecret`
14. The last parameter will be the sharepoint 'slug', this is part of the url of the sharepoint site what you want to use and creation of sharepoint site is out of scope of this readme.  
  When you sharepoint url is like `https://{tenant}.sharepoint.com/sites/{site-slug}/Shared%20Documents/Forms/AllItems.aspx`  
  You need to set the `$sharepointSite` as `{site-slug}`    
    
    Example:    
     - Sharepoint site url: `https://GWSN.sharepoint.com/sites/gwsn-documents-store/Shared%20Documents/Forms/AllItems.aspx`
     - Sharepoint site variable:  `$sharepointSite = 'gwsn-documents-store'`
   

``` php
use GWSN\FlysystemSharepoint\FlysystemSharepointAdapter;
use GWSN\FlysystemSharepoint\SharepointConnector;
use League\Flysystem\Filesystem;

$tenantId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$clientId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$clientSecret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
$sharepointSite = 'your-path-to-your-site';

$connector = new SharepointConnector($tenantId, $clientId, $clientSecret, $sharepointSite);

$prefix = '/test'; // optional
$adapter = new FlysystemSharepointAdapter($connector, $prefix);


$flysystem = new Filesystem($adapter);
```

## Testing

``` bash
$ composer run-script test
```

## Security

If you discover any security related issues, please email info@gwsn.nl instead of using the issue tracker.


## Laravel

To use the flysystem in Laravel there are additional steps required:

First we need to create a `FlySystemSharepointProvider` and register this in the `config/app.php`

Then we need to create the config into the `config/filesystem.php`

### Create the FlySystemSharepointProvider

we need to create a provider to register the custom FlySystem Adapter

create a new file in the `app/Providers` directory called `FlySystemSharepointProvider.php` with the following content:
```PHP
<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use GWSN\FlysystemSharepoint\FlysystemSharepointAdapter;
use GWSN\FlysystemSharepoint\SharepointConnector;

class FlySystemSharepointProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() { }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('sharepoint', function ($app, $config) {

            $adapter = new FlysystemSharepointAdapter(new SharepointConnector(
                    $config['tenantId'],
                    $config['clientId'],
                    $config['clientSecret'],
                    $config['sharepointSite'],
                ),
                $config['prefix'],
            );

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
```

### Register the provider in the App config
Add the bottom of the list with providers we need to add the previous created Provider:

```php 
 'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
         [...]
         App\Providers\FlySystemSharepointProvider::class,
 ]
```

### Update the Filesystem config

Add filesystem Disks section we will add a new custom disk: sharepoint.

We use env variables as config but you could also enter them directly as string

```php 
 /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'sharepoint' => [
            'driver' => 'sharepoint',
            'tenantId' => env('SHAREPOINT_TENANT_ID', 'secret'),
            'clientId' => env('SHAREPOINT_CLIENT_ID', 'secret'),
            'clientSecret' => env('SHAREPOINT_CLIENT_SECRET_VALUE', 'secret'),
            'sharepointSite' => env('SHAREPOINT_SITE', 'laravelTest'),
            'prefix' => env('SHAREPOINT_PREFIX', 'test'),
        ]

    ],
```

### Usage in laravel 
it is bad practice to use logic into a controller but for example purpose we show it in the controller:

`App\Http\Controllers\Controller.php`
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index() {

        try {
            Storage::disk('sharepoint')->put('test.txt', 'testContent');
            return Storage::disk('sharepoint')->get('test.txt');
            
        } catch (\Exception $exception) {
            dd($exception);
        }
        return 'error';
    }
}
```



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
