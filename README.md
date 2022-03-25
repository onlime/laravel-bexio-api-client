# Laravel Bexio API PHP Client

The bexio API Client Library enables you to work with the bexio API. **This is a wrapper around [onlime/bexio-api-client](https://github.com/onlime/bexio-api-client) for easier Laravel integration using [Laravel HTTP Client](https://laravel.com/docs/9.x/http-client).** You could use this in combination with my zero-configuration [Laravel HTTP Client Global Logger](https://github.com/onlime/laravel-http-client-global-logger) for detailed request/response logging.

See [onlime/bexio-api-client README](https://github.com/onlime/bexio-api-client/blob/main/README.md) and the official [bexio API documentation](https://docs.bexio.com) for more information how to use the API.

## Installation

You can use **Composer** to integrate the library into your Laravel project:

```sh
$ composer require onlime/laravel-bexio-api-client
```
## Sample Usage

> **NOTE:** I am just documenting Laravel project integration here. Please consult the [onlime/bexio-api-client](https://github.com/onlime/bexio-api-client) README for Bexio API Client library documentation.

I recommend to make this thing configurable in `config/bexio.php`:

```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Bexio API Credentials
    |--------------------------------------------------------------------------
    */
    'api' => [
        'clientId' => env('BEXIO_API_CLIENT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'),
        'clientSecret' => env('BEXIO_API_CLIENT_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        ),
        'tokensFile' => env('BEXIO_API_TOKENS_FILE', 'bexio_client_tokens.json'),
        'scopes' => explode(' ', env('BEXIO_API_SCOPES',
            'openid profile contact_edit offline_access kb_invoice_edit article_edit note_edit'
        )),
    ],
];
```

Put your Bexio API credentials from [Bexio Developer Portal](https://developer.bexio.com/) into your `.env`:

```bash
BEXIO_API_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
BEXIO_API_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Create some secure storage to store the client (access and refresh) tokens, `config/filesystems.php`:

```php
    'disks' => [
        // ...
        'secure' => [
            'driver' => 'local',
            'root' => storage_path('app/secure'),
            'visibility' => 'private',
            'throw' => false,
        ],
```

You could then define these two routes in your `routes/web.php`:

- `GET /admin/bexio/auth` – authenticate against Bexio and generate your access and refresh tokens
- `GET /admin/bexio/demo` – demo page to test Bexio API with the previously generated access token

```php
// routes/web.php
<?php
Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::controller(BexioController::class)->prefix('bexio')->group(function () {
            Route::redirect('/', '/admin/bexio/auth');
            Route::get('/auth', 'authenticate')->name('bexio.auth');
            Route::get('/demo', 'demo')->name('bexio.demo');
        });
    });
});
```

> **WARNING:** Make sure you protect those rules and only make it available to your admin users!

Now create the controller, `php artisan make:controller BexioController`:

```php
<?php

namespace App\Http\Controllers;

use Bexio\Resource\Contact as BexioContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use LaravelBexio\Client;

class BexioController extends Controller
{
    public function authenticate(Request $request)
    {
        $client = $this->getBexioClient();
        $client->authenticate(config('bexio.api.scopes'), route('bexio.auth'));
        $client->persistTokens($this->getTokensFile());

        return view('bexio.auth');
    }

    public function demo(Request $request)
    {
        $client = $this->getBexioClient();
        $client->loadTokens($this->getTokensFile());

        return view('bexio.demo', [
            'contacts' => (new BexioContact($client))->getContacts(),
        ]);
    }

    protected function getBexioClient(): Client
    {
        return new Client(
            config('bexio.api.clientId'),
            config('bexio.api.clientSecret')
        );
    }

    protected function getTokensFile(): string
    {
        return Storage::disk('secure')->path(config('bexio.api.tokensFile'));
    }
}
```

> **NOTE:** Make sure you add http://localhost:8080/admin/bexio/auth to the redirect URLs in [Bexio Developer Portal](https://developer.bexio.com/) 

Now fire up your application:

```php
$ php artisan serve
```

Then, access the Bexio authentication page:

- http://localhost:8080/admin/bexio/auth

This will ask you to confirm the requested scopes and you will need to login with your Bexio credentials in case you haven't done this yet.

Once you have authenticated, you'll see the contents of the `bexio.auth` view, which could display some confirmation message and ask you to proceed to the demo page. The demo page on http://localhost:8080/admin/bexio/demo will then use the Bexio API client tokens which were stored in `<project-root>/storage/app/secure/bexio_client_tokens.json`, and display all Bexio contacts (as an example, to verify all is working).

## Authors

Author of this awesome package is [Philip Iezzi (Onlime GmbH)](https://www.onlime.ch/).

## License

This package is licenced under the [MIT license](LICENSE) however support is more than welcome.
