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
define('DB_NAME', 'resep');

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
define('AUTH_KEY',         'sf+[[B?.a)sjua!}R^g%.h2OD>k8B#3LOtY&EL.5}.p1_Le*bNc<(ka(_2)?n$VD');
define('SECURE_AUTH_KEY',  'S1U_G0quhB$E7+K?6z9[-KqtpxXj!7`fh)[8Z)&MlWi}C`pUd*7-N+=oHfGBmK)v');
define('LOGGED_IN_KEY',    'WMWc6At02??^K n]9]yzBx||5Qk[rtnZ2pw]#OGb:+_q#y/|!j{1vR>H3d|oh4_@');
define('NONCE_KEY',        'wI6eV=Vp-O%:E0~Y-@`6V9A&#R2zVn3nZoXKk_IhUfp*)-AoR/-R}cxObiPmR#;T');
define('AUTH_SALT',        '>);3A-5k2kCN[z}ap(*iKSI~%nG(!B5fz_Bfa2D<QyjY4xc,-psO*e_x2z.Xu%q&');
define('SECURE_AUTH_SALT', '-YMew%)dC2YhW>S]bct*n;#1ZP+-X?^GQ(A8C:r%t_E_Q]O&vZ[!W(F^5v7k,`0O');
define('LOGGED_IN_SALT',   '4, `#<9ib@AtN``x?hRDQ7+)(;>7X]/J=y<U3)cO|NA)(/de+GS`>]G{,gA_ldtd');
define('NONCE_SALT',       '$+0hg$KxaS1Mm-q d##I1Z;M}*f2<mf0;,9PF7x(+&i_|V+3$?/h40qGLHZu3kJF');

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