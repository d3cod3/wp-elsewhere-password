<?php

/**
 * Plugin Name: WP ELSEWHERE Password
 * Plugin URI:  https://elsewhere.d3cod3.org
 * Description: Harden password encryption mechanism for wordpress.
 * Author:      n3m3da
 * Author URI:  http://d3cod3.org
 * Version:     1.0
 * Licence:     MIT
 */

/* Requires PHP 5.5.0 or newer */

// IMPORTANT, we need to alter two ROW type of wp_users table in order to have this plugin working:
// 1 - wordpress have DEFAULT '0000-00-00 00:00:00' for user_registered ROW, this will generate an SQL error at every intent of modify
//     the wp_users structure, so we need to change this value to a correct one, for example '0001-01-01 00:00:00'
// 2 - the standard wordpress user_pass ROW is a varchar(255) and is not enough for our harden encrypted passwords, we'll need a varchar(300)
//
// This are the mysql query:
//
// 1 - ALTER TABLE wp_users MODIFY COLUMN user_registered datetime NOT NULL DEFAULT '0001-01-01 00:00:00';
// 2 - ALTER TABLE wp_users MODIFY COLUMN user_pass varchar(300) NOT NULL DEFAULT '';


// Deny file direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)){ die('No direct access allowed. You a nasty one!'); }

// import crypto password lock key ELSEWHERE_KEY, stored outside the server document root, really useful when PHP and DB are on different hardware
require_once($_SERVER['DOCUMENT_ROOT'].'/../wp-crypto.php');

require_once(__DIR__ . "/libs/constant_time_encoding/Autoload.php");    // constant_time_encoding   [https://github.com/paragonie/constant_time_encoding/tree/v1.x]
require_once(__DIR__ . "/libs/php-encryption/CryptoAutoload.php");      // php-encryption           [https://github.com/defuse/php-encryption]

use \ParagonIE\ConstantTime\Base64;
use \Defuse\Crypto\Crypto;
use \Defuse\Crypto\Key;

const WP_HASH_PREFIX    = '$P$';

/**
 * 1. VerifyHMAC-then-Decrypt the ciphertext to get the hash
 * 2. Verify that the password matches the hash
 *
 * @global PasswordHash $wp_hasher PHPass object used for checking the password
 *  against the $hash + $password
 * @uses PasswordHash::CheckPassword
 *
 * @param string     $password Plaintext user's password
 * @param string     $hash     Hash of the user's password to check against.
 * @param string|int $user_id  Optional. User ID.
 * @return bool False, if the $password does not match the hashed password
 */
function wp_check_password($password, $hash, $userId = ''){
    if (strpos($hash, WP_HASH_PREFIX) === 0) {
        global $wp_hasher;
        if (empty($wp_hasher)) {
            require_once(ABSPATH . WPINC . '/class-phpass.php');
            $wp_hasher = new PasswordHash(8, true);
        }
        $check = $wp_hasher->CheckPassword($password, $hash);
        if ($check && $userId) {
            $hash = wp_set_password($password, $userId);
        }
    }

    $plain = Crypto::decrypt($hash,Key::loadFromAsciiSafeString(ELSEWHERE_KEY));
    $check = password_verify(Base64::encode(hash('sha512',$password,true)), $plain);

    return apply_filters('check_password', $check, $password, $plain, $userId);
    
}

/**
 * 1. Hash password using bcrypt-base64-SHA512
 * 2. Encrypt-then-MAC the hash
 *
 * @param string $password Plain text user password to hash
 * @return string The hash string of the password
 */
function wp_hash_password($password){
    $options = apply_filters('wp_hash_password_options', ['cost' => 12]);
    $hash = password_hash(Base64::encode(hash('sha512',$password,true)),PASSWORD_DEFAULT,$options);
    $crypted = Crypto::encrypt($hash,Key::loadFromAsciiSafeString(ELSEWHERE_KEY));

    return $crypted;
}

/**
 * Updates the user's password with a new encrypted one.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $password The plaintext new user password
 * @param int    $user_id  User ID
 */
function wp_set_password($password, $userId){
    global $wpdb;

    $hash = wp_hash_password($password);
    $wpdb->update($wpdb->users, ['user_pass' => $hash, 'user_activation_key' => ''], ['ID' => $userId]);

    wp_cache_delete($userId, 'users');
}

?>
