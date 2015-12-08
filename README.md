# Bitbucket Provider for OAuth 1.0 Client
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth1-bitbucket.svg?style=flat-square)](https://github.com/thephpleague/oauth1-bitbucket/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth1-bitbucket/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth1-bitbucket)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/oauth1-bitbucket.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth1-bitbucket/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/oauth1-bitbucket.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth1-bitbucket)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth1-bitbucket.svg?style=flat-square)](https://packagist.org/packages/league/oauth1-bitbucket)

This package provides Bitbucket OAuth 1.0 support for the PHP League's [OAuth 1.0 Client](https://github.com/thephpleague/oauth1-client).

## Installation

To install, use composer:

```
composer require league/oauth1-bitbucket
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth1\Client\Server\Bitbucket` as the server.

### Authenticating with OAuth 1.0

```php
// Create a server instance.
$server = new \League\OAuth1\Client\Server\Bitbucket([
    'identifier'              => 'your-identifier',
    'secret'                  => 'your-secret',
    'callbackUri'             => 'http://your-callback-uri/',
]);

// Obtain Temporary Credentials and User Authorization
if (!isset($_GET['oauth_token'], $_GET['oauth_verifier'])) {

    // First part of OAuth 1.0 authentication is to
    // obtain Temporary Credentials.
    $temporaryCredentials = $server->getTemporaryCredentials();

    // Store credentials in the session, we'll need them later
    $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
    session_write_close();

    // Second part of OAuth 1.0 authentication is to obtain User Authorization
    // by redirecting the resource owner to the login screen on the server.
    // Create an authorization url.
    $authorizationUrl = $server->getAuthorizationUrl($temporaryCredentials);

    // Redirect the user to the authorization URL. The user will be redirected
    // to the familiar login screen on the server, where they will login to
    // their account and authorize your app to access their data.
    header('Location: ' . $authorizationUrl);
    exit;

// Obtain Token Credentials
} else {

    try {

        // Retrieve the temporary credentials we saved before.
        $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);

        // We will now obtain Token Credentials from the server.
        $tokenCredentials = $server->getTokenCredentials(
            $temporaryCredentials,
            $_GET['oauth_token'],
            $_GET['oauth_verifier']
        );

        // We have token credentials, which we may use in authenticated
        // requests against the service provider's API.
        echo $tokenCredentials->getIdentifier() . "\n";
        echo $tokenCredentials->getSecret() . "\n";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $server->getResourceOwner($tokenCredentials);

        var_export($resourceOwner->toArray());

        // The server provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $server->getAuthenticatedRequest(
            'GET',
            'http://your.service/endpoint',
            $tokenCredentials
        );

    } catch (\League\OAuth1\Client\Exceptions\Exception $e) {

        // Failed to get the token credentials or user details.
        exit($e->getMessage());

    }

}
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```
``` bash
$ ./vendor/bin/phpcs src --standard=psr2 -sp
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/oauth1-bitbucket/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/thephpleague/oauth1-bitbucket/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth1-bitbucket/blob/master/LICENSE) for more information.
