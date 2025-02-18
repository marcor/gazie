<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-present - Antonio De Vincentiis Montesilvano (PE)
  (https://www.devincentiis.it)
  <https://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

/**
 * Configuration for: Database Connection
 * This is the place where your database login constants are saved
 *
 * For more info about constants please @see https://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see https://stackoverflow.com/q/2447791/1114320
 *
 * DB_HOST: database host, usually it's "127.0.0.1" or "localhost", some servers also need port info
 * DB_NAME: name of the database. please note: database and database table are not the same thing
 * DB_USER: user for your database. the user needs to have rights for SELECT, UPDATE, DELETE and INSERT.
 *          by the way, it's bad style to use "root", but for development it will work.
 * DB_PASS: the password of the above user
 */
/* GAZIE MOD BEGIN */

//  se sono loggato  le impostazioni della lingua dal server

$server_lang = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr(strtoupper($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2) : '';
if (isset($local['cvalue'])) { // se ho il valore della lingua di localizzazione lo uso
	$server_lang = substr(strtoupper($local['cvalue']), 0, 2);
}
switch ($server_lang) {
    case 'IT':
        define("TRANSL_LANG", 'italian');
        break;
    case 'EN':
        define("TRANSL_LANG", 'english');
        break;
    case 'ES':
        define("TRANSL_LANG", 'spanish');
        break;
    default:
        define("TRANSL_LANG", 'italian');
        break;
}

define("DB_HOST", $Host);
define("DB_NAME", $Database);
define("DB_TABLE_PREFIX", $table_prefix);
define("DB_USER", $User);
define("DB_PASS", $Password);



/* GAZIE MOD END */

/**
 * Configuration for: Cookies
 * Please note: The COOKIE_DOMAIN needs the domain where your app is,
 * in a format like this: .mydomain.com
 * Note the . in front of the domain. No www, no http, no slash here!
 * For local development .127.0.0.1 or .localhost is fine, but when deploying you should
 * change this to your real domain, like '.mydomain.com' ! The leading dot makes the cookie available for
 * sub-domains too.
 * @see https://stackoverflow.com/q/9618217/1114320
 * @see https://www.php.net/manual/en/function.setcookie.php
 *
 * COOKIE_RUNTIME: How long should a cookie be valid ? 1800 seconds = 30 min
 * COOKIE_DOMAIN: The domain where the cookie is valid for, like '.mydomain.com'
 * COOKIE_SECRET_KEY: Put a random value here to make your app more secure. When changed, all cookies are reset.
 */
define("COOKIE_RUNTIME", 40);
define("COOKIE_DOMAIN", $Host);

// l'autentica tramite cookie non lo utilizzo comunque l'ho SPOSTATO SULLA TABELLA gaz_config del database
//define("COOKIE_SECRET_KEY", "1gp@GaZi{+$78sfpMJFe-92s");

/**
 * Configuration for: Email server credentials
 *
 * Here you can define how you want to send emails.
 * If you have successfully set up a mail server on your linux server and you know
 * what you do, then you can skip this section. Otherwise please set EMAIL_USE_SMTP to true
 * and fill in your SMTP provider account data.
 *
 * An example setup for using gmail.com [Google Mail] as email sending service,
 * works perfectly in August 2013. Change the "xxx" to your needs.
 * Please note that there are several issues with gmail, like gmail will block your server
 * for "spam" reasons or you'll have a daily sending limit. See the readme.md for more info.
 *
 * It's really recommended to use SMTP!
 *
 *
 *
 */
define("EMAIL_USE_SMTP", true);
define("EMAIL_SMTP_AUTH", true);

function getBaseUrl() { // ricavo l'URL del modulo
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];
    // output: https://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'https://';
    // return: https://localhost/myproject/
    return $protocol . $hostName . $pathInfo['dirname'] . "/";
}

/**
 * Configuration for: password reset email data
 * Set the absolute URL to password_reset.php if necessary for email password reset links
 */
define("EMAIL_PASSWORDRESET_URL", getBaseUrl()."login_password_reset.php");

/**
 * Configuration for: verification email data
 * Set the absolute URL to register.php if necessary for email verification links
 */
define("EMAIL_VERIFICATION_URL", getBaseUrl()."login_register.php");

/**
 * Configuration for: Hashing strength
 * This is the place where you define the strength of your password hashing/salting
 *
 * To make password encryption very safe and future-proof, the PHP 5.5 hashing/salting functions
 * come with a clever so called COST FACTOR. This number defines the base-2 logarithm of the rounds of hashing,
 * something like 2^12 if your cost factor is 12. By the way, 2^12 would be 4096 rounds of hashing, doubling the
 * round with each increase of the cost factor and therefore doubling the CPU power it needs.
 * Currently, in 2013, the developers of this functions have chosen a cost factor of 10, which fits most standard
 * server setups. When time goes by and server power becomes much more powerful, it might be useful to increase
 * the cost factor, to make the password hashing one step more secure. Have a look here
 * (@see https://github.com/panique/php-login/wiki/Which-hashing-&-salting-algorithm-should-be-used-%3F)
 * in the BLOWFISH benchmark table to get an idea how this factor behaves. For most people this is irrelevant,
 * but after some years this might be very very useful to keep the encryption of your database up to date.
 *
 * Remember: Every time a user registers or tries to log in (!) this calculation will be done.
 * Don't change this if you don't know what you do.
 *
 * To get more information about the best cost factor please have a look here
 * @see https://stackoverflow.com/q/4443476/1114320
 *
 * This constant will be used in the login and the registration class.
 */
define("HASH_COST_FACTOR", "10");
// per GAzie aes_key (colonne criptate)
define("AES_KEY_SALT","CK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNoCK4OGOAtec0zgbNo");
define("AES_KEY_IV","LQjFLCU3sAVplBC3");

