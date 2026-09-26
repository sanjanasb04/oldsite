<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u133071818_kaorr' );

/** Database username */
define( 'DB_USER', 'u133071818_kaorr' );

/** Database password */
define( 'DB_PASSWORD', '1!uF5otX3+' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'YBtGFBf3[_>VzKPC,]jf8HeAY&t!d{u6;*`Klr>fPcH+aoNHvk^>Rn<T/np45x6}' );
define( 'SECURE_AUTH_KEY',   '@X[v5O@61-t$ncoQ~[QK[}*r)SUr(nC4mO8+M/7NWW,hG|0m!@ kz-a}ESS:rFQm' );
define( 'LOGGED_IN_KEY',     '}jPB[B3vyaNpR3x!iFciva&2_UO]9,[$>dS3y|,y!P*PYlpnR[V#u}2[JRI,8?~m' );
define( 'NONCE_KEY',         'H&,=<Z VKsLc&xN)LFf,`Q+4-C(:*m*P@,P9msA[h%!+1y@F;Ezz2C]aR#n|xPJg' );
define( 'AUTH_SALT',         'O +9`>ne e^$Y`R;6y19$`}s:X(Sa>GpFhQj7xt{L*iaU}.3:6xT}15;GTe,H!b6' );
define( 'SECURE_AUTH_SALT',  'Mp8#4OqrCf8eewmBle|+r;YJ-j,?gY_o|9!!6GW<4ku)u)tQW?a~E*mtp]f,z{?(' );
define( 'LOGGED_IN_SALT',    '3l1*ZE$q7jo!)U99iSvhsa<=n$gmAk~Q0r>(9 [^XtVMK7yMLvPySWWJ|_lF8/1{' );
define( 'NONCE_SALT',        'o?L0%_D0:}qnLuSdPg507i`D!r|~UnTY6G:EIVBNT8=q/{Y=PX~U8x5l=~a:F-CV' );
define( 'WP_CACHE_KEY_SALT', '!tnF_46FaS ^GEh220RIbe/} U5[gXY^Ih|XOt=:Lp`4@S>aP%pN3EG/f:}ZMWkg' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpwx_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
