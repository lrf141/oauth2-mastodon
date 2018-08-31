# oauth2-mastodon

[![Latest Stable Version](https://poser.pugx.org/lrf141/oauth2-mastodon/v/stable)](https://packagist.org/packages/lrf141/oauth2-mastodon)
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/lrf141/oauth2-mastodon.svg?branch=master)](https://travis-ci.org/lrf141/oauth2-mastodon)
[![Code Coverage](https://scrutinizer-ci.com/g/lrf141/oauth2-mastodon/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lrf141/oauth2-mastodon/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lrf141/oauth2-mastodon/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lrf141/oauth2-mastodon/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/lrf141/oauth2-mastodon.svg?style=flat-square)](https://packagist.org/packages/lrf141/oauth2-mastodon)


## Install

Via Composer

``` bash
$ composer require lrf141/oauth2-mastodon
```

## Usage

``` php
<?php

use Lrf141\OAuth2\Client\Provider\Mastodon;

session_start();

$provider = new Mastodon([
    'clientId' => '',
    'clientSecret' => '',
    'redirectUri' => 'redirect url',
    'instance' => 'https://mstdn.jp',
    'scope' => 'read write follow',
]);


if (!isset($_GET['code'])) {

    $authUrl = $provider->getAuthorizationUrl();

    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    // Optional: Now you have a token you can look up a users profile data
    try {
    
        $user = $provider->getResourceOwner($token);
       
        echo $user->getName();
        
    } catch(Exception $e) {
       
       
        exit('Oh dear...');
    }


    echo $token->getToken();
}

```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email ghost141.kentyo @ gmail.com instead of using the issue tracker.

## Credits

- [K.Takeuchi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/:vendor/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-travis]: https://travis-ci.org/:vendor/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/:vendor/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/:vendor/:package_name
[link-downloads]: https://packagist.org/packages/:vendor/:package_name
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors
