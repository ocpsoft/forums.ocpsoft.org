<?php
/** 
 * The base configurations of bbPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys and bbPress Language. You can get the MySQL settings from your
 * web host.
 *
 * This file is used by the installer during installation.
 *
 * @package bbPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for bbPress */
define( 'BBDB_NAME', 'ocpwpdb' );

/** MySQL database username */
define( 'BBDB_USER', 'ocpdbuser' );

/** MySQL database password */
define( 'BBDB_PASSWORD', '0cpdbp4ss' );

/** MySQL hostname */
define( 'BBDB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'BBDB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'BBDB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/bbpress/ WordPress.org secret-key service}
 *
 * @since 1.0
 */
define('BB_AUTH_KEY',        'yI]P`Y:*tB~s{pTdRP2!TS.AizV|`h]u-v1iWTNScMpWuENo7eR-DeULv~ivZs9Q');
define('BB_SECURE_AUTH_KEY', '%~[s2qKb(I66Ad^BOA=e@H3[;eOZ+M~O.Rs*rE(+n^BzqIZhCE0 ihN<u1e+C,rC');
define('BB_LOGGED_IN_KEY',   '(YGiE9)>8%n e[9xR&*!}F:<+]1eG++x|!.*/]_-f*R`sl ;PxPk^dK:W g];5iR');
define('BB_NONCE_KEY',       'tqpDXJKxvf_7}_jT3_2>RQ$.m,#x@)KDLjGNfd^|x[R{$Tg9|1B|c=E9|A- u;4$');
/**#@-*/

/**
 * bbPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$bb_table_prefix = 'bb_';

/**
 * bbPress Localized Language, defaults to English.
 *
 * Change this to localize bbPress. A corresponding MO file for the chosen
 * language must be installed to a directory called "my-languages" in the root
 * directory of bbPress. For example, install de.mo to "my-languages" and set
 * BB_LANG to 'de' to enable German language support.
 */
define( 'BB_LANG', '' );
?>
