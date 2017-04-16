# wp-elsewhere-password

wp-elsewhere-password is a WordPress plugin to harden his password encryption mechanism.

This plugin requires PHP >= 5.5.0 which introduced the built-in [`password_hash`](http://php.net/manual/en/function.password-hash.php) and [`password_verify`](http://php.net/manual/en/function.password-verify.php) functions.

## Requirements

* PHP >= 5.5.0
* WordPress >= 4.4 (see https://core.trac.wordpress.org/ticket/33904)

## Installation

Manually copy `libs/` folder and `wp-elsewhere-password.php` into your `mu-plugins` folder, [Must Use Plugins](https://codex.wordpress.org/Must_Use_Plugins).

Manually copy `wp-crypto.php` elsewhere, a good choice is copying it outside your server document root and then include it like this:

```php
require_once($_SERVER['DOCUMENT_ROOT'].'/../wp-crypto.php');
```

You'll need to generate your personal encryption key, and add it to `wp-crypto.php`. To do that create a temporary php file like this:

```php
<?php

require_once(__DIR__ . "/libs/php-encryption/CryptoAutoload.php");

echo \Defuse\Crypto\Key::CreateNewRandomKey()->saveToAsciiSafeString();

 ?>
```

Open it in your browser to generate an encryption key, copy-paste it in `wp-crypto.php` and save the file.

## Libraries

This plugin use the following libraries:

[constant_time_encoding](https://github.com/paragonie/constant_time_encoding/tree/v1.x) To prevent leak information about what you are encoding/decoding via processor cache misses

and

[php-encryption](https://github.com/defuse/php-encryption) To ensure the use of a secure encryption mechanism.

## Thanks

Thanks to [wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt) for a first introduction to the issue.
