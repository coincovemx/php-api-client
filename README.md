
# Volabit PHP Client

Volabit's API library for PHP. Integrate the Volabit services in your apps with ease.

You can see the available methods on the [project wiki][wiki]. Details of the API use can be found on the [official page][api-docs].

## Installation

Using Composer:

    $ composer require volabit/client

## Usage

1) Instance a new Volabit client object.

```php
$app_id      = 'The registered API for your APP.';
$secret      = 'The registered secret for your APP.';
$callback    = 'The registered callback URL for your APP';

$volabit_client = new Volabit\Client($app_id, $secret, $callback);
```

Note that the by default the Volabit client uses the **production** environment. If you want to use the **test** environment, set the sandbox flag to `true` before requesting the authorization code.

```php
$volabit_client->sandbox(true);
```

2) Get the URL that will allow the user to authorize your app to use his/her account. (It should be opened in a browser.)

```php
$auth_url = $volabit_client->authorize()
```

3) After you get the authorization code (sent at the callback URL that you provided), you'll use it to get the refresh and access tokens. This code can be used only once, so be sure to store the token object for later use or your app will have to be reauthorized.

```php
$volabit_client->getToken('The given authorization code.');
```

4) With these tokens, you'll be ready to call the services. The methods will return a response array.

```php
$response = $volabit_client->tickers();
print_r($response);
// Array
// (
//    [btc_usd_buy] => 236.42
//    [btc_usd_sell] => 236.51
//    [usd_mxn_buy] => 14.59
//    [usd_mxn_sell] => 15.19
//    [btc_mxn_buy] => 3450.44
//    [btc_mxn_sell] => 3592.64
// )
```

You can see the available methods source on the [corresponding section][source] of the client. Details of the API use can be found on the [official page][api-docs].

## Contributing

1. Fork it ( https://github.com/[my-github-username]/php-client/fork )
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create a new Pull Request


[source]: https://github.com/coincovemx/php-client/blob/master/src/Volabit.php
[wiki]: https://github.com/coincovemx/php-client/wiki
[api-docs]: https://coincovemx.github.io/
