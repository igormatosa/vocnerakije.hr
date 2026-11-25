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
define( 'DB_NAME', 'vocne-rakije' );

/** Database username */
define( 'DB_USER', 'imatosa' );

/** Database password */
define( 'DB_PASSWORD', 'iMatosA84' );

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
define( 'AUTH_KEY',         'qO5maJSx>%(Hl;HwD#ZaH7KR=M[d5:<m_X(-<Ic#&v^vQiw0]mvK-56N*_KNkT?G' );
define( 'SECURE_AUTH_KEY',  'Ys+nhX/0WG&_:f:AlC&A09&%Lq5|`ol?91vr13a RNvqDcP*qmpzL-;ZvjiSXKX9' );
define( 'LOGGED_IN_KEY',    '{l;+WFtT}jAZw1 a;$8aiPDBqqW.Q]k2#iGN-?#z9pTWV!frevvy4s+yg7W/ Dad' );
define( 'NONCE_KEY',        'dAtHf9DD126t*/lb=Ocs9~r!7i}S[wJ3~XwOzIvm|=E-0ZPagJ-=KmDxWg`(8&Np' );
define( 'AUTH_SALT',        '7zOm#l%<{f?QY0rjoreJJ>xjHsw 4#BtQiDF|Y.f*>!)st!$pQ</#@vbuxr{<{dt' );
define( 'SECURE_AUTH_SALT', '>6kzgP1-zq<t0rl2QBh[@-zfWfU+2{I-~t|6<d[XZt`:^wY<F^v%nj)g<stL#!ks' );
define( 'LOGGED_IN_SALT',   '}9hdL.p|3a{DnN}?R)Z1M>ku6s?%~<#;!>?~5w9udO<oH`~Yf`l5=ZNn75/fJy|M' );
define( 'NONCE_SALT',       '=&s&urq&*bU rDRC(eDHPW`nj>//8UZk_&OKP]fWl:0k-`K$rBodtqb@)r$0 jc>' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'vr_';

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
