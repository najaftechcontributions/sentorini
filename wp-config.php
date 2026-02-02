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
define( 'DB_NAME', 'sentorini' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',         'PC,RsKj/.@O{yxKT=3Epwq|8)WPqZ*t@P^c3v0!uv(T>6bReriHL&B}@`{6@1;@R' );
define( 'SECURE_AUTH_KEY',  'xl~*F;hc)Q@VN{1VsCMcy.su%9~yCv)4v>z9QvFQOcB[eRUrPF7* H2?<PWa[r.&' );
define( 'LOGGED_IN_KEY',    'pQ!1muIe] O`n4@n<#x Zl.4%dkc.sm#V*KdFJCqAeZHv#M!*uU5GWV`-bk-RNxZ' );
define( 'NONCE_KEY',        'L0nfD/upwfx=`*b&|oAL#(ke@{=a1A&u17XvA?k*yyUt4HE[YJacVx[&i};L<[VA' );
define( 'AUTH_SALT',        'YGO>G1dk4,eY.; tq#n=M*l{m[|4u_M+^&{{iK^<:9Ez5Fl%3wV9n.,~]FY~@H:b' );
define( 'SECURE_AUTH_SALT', 'o05joiCZaAzh.UAxK?MOl:=2k!YeDE@lG=XG{D8N4lK`@wfGW%lL`a7O|Ur^k)_Z' );
define( 'LOGGED_IN_SALT',   '.d>4yj-^tycN2<Y19kKM SOF.JwDe.`qOQ[W/n[&]=f!90Ls6~YOQ{`A<s.b2oZN' );
define( 'NONCE_SALT',       's$4Oak!O[Jt48N?-c[n/^Dbb{Dq_$jL!2&Fn_TYc3FSRXexu 7#aV`I2P/c*st40' );

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
$table_prefix = 'wp_';

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
