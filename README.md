# wp-elsewhere-password

wp-elsewhere-password is a WordPress plugin to replace WP's outdated and insecure MD5-based password hashing with the modern and secure

This plugin requires PHP >= 5.5.0 which introduced the built-in [`password_hash`](http://php.net/manual/en/function.password-hash.php) and [`password_verify`](http://php.net/manual/en/function.password-verify.php) functions.

## Requirements

* PHP >= 5.5.0
* WordPress >= 4.4 (see https://core.trac.wordpress.org/ticket/33904)