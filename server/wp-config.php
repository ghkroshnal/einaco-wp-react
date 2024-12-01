<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'smart_server' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'G1K{KcxPD/J$w%{^R+E,I4W^1W.8*K~6sc*{/`I=Kg!yG`;(bH@TPXPBW8;m&a/$' );
define( 'SECURE_AUTH_KEY',  ';LeUz;{t( kE{z;6bBI%xbyr->-P/M=LV_oXKc8Yiv!#2m_DN/he.m`Njq)vFj5|' );
define( 'LOGGED_IN_KEY',    'zX=tE%wIkt{nx2t_u?/ (@m;tDr5o6nfANI~+X:VUdhxoje2c/1A0n(CR!apup]u' );
define( 'NONCE_KEY',        '(s8|#1XqXBS134X$SJ]O$VLo_1R=x):4WO_F#/ke^||aKDg)H!kY/K~qO>G9{&%k' );
define( 'AUTH_SALT',        '/@Ct;~YR~c2Rj4h5?)jr<6(dbU0`>mxO3sHFEMn1A=pfTrl&[I!3$bPNX-l]@SY|' );
define( 'SECURE_AUTH_SALT', 'I^Ps=#5:K t4wac!$^|GI?KZ*<%<SH94[.zw@x`RzT-HY~E:?Ir^S`D-jhZP.Q$R' );
define( 'LOGGED_IN_SALT',   '~B>/@?h5}&)t~T8s@18g|mH{MN_Aa|[lUdS{nSkjYboSnYx]fzF_;;sad;<*m&Jb' );
define( 'NONCE_SALT',       '~pO|#KL%>pnn4=}cMgtx^RFPs(y!]m/|og7!Z~S=.em<Q@O*0,~g )VrpS#7=]AM' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_smart_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
