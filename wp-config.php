<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '>y7-:]1M#+>>4DqCL!|fO(B>S#^BWj!*X}wf5{ ]>fzVjn6^.4WXV|&vvh;H@m>r');
define('SECURE_AUTH_KEY',  '<W6SFTE3*o(94avk;cwc9|?T^`{(/!J~ZdNM}w4mLaqN>^9nn50i{6&|SLK|0g8U');
define('LOGGED_IN_KEY',    'Nd)EFb-)8v>{h?&#n=87lD`1TOu2P+15pr`d:&-C0*|H*S~/kCH2mC2q-~oCl`~V');
define('NONCE_KEY',        '[e{`v}+p,?2byhz^C&INN=u>?h $t;(kal7 e27)JYc6.]1t;WX@}Ij1iuuGkG[e');
define('AUTH_SALT',        '8<5k@[kKRV)?ZFw:(wQ5y;KWga]{,cniZL)m|vHNTtUa/}($w({f8C3R`/9K0rX ');
define('SECURE_AUTH_SALT', '+nsla%<Zlj8MrH{8PVa@nLTJ>V(C|H-WU8RAF`]-)tEv27/kx<f_UC9,|J0)P`NN');
define('LOGGED_IN_SALT',   'V*}?@~$;A`43Z@9FAEyM9{*E 9X9W9x4MH@oC!9mYOK.5r~?I[5_+l5PVzXe+3By');
define('NONCE_SALT',       '2B{J1i~aefRs*VHm%|VRP%P/<C$Uh(,/U{GCa8l&P[1dx1h?{uFq ylqDdaVNj~Q');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
