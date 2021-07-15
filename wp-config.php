<?php
define( 'WP_CACHE', true ); // Added by WP Rocket


 
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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gryqssejnm' );
/** MySQL database username */
define( 'DB_USER', 'gryqssejnm' );
/** MySQL database password */
define( 'DB_PASSWORD', 'kjddJKHJ_fkd23' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '|iA3B56D8CH5&[kiu#AEAlvr$7SS=!d`FcsU^6gM`z[*;a]s&<#$X00l+k!_0Y~p' );
define( 'SECURE_AUTH_KEY',  '37|bB0NGfgs>}Y|6Z+S %b@&wl{7%Nq2HyeTHj9<zEl=50_<b7i7};wS@S_7Ndbv' );
define( 'LOGGED_IN_KEY',    '@CM&acHh/zXFP)$W?uHUNzWL#d^pp/~}^z46P`EW=S>Dm;bcyzeR0#~#_SL_.;)>' );
define( 'NONCE_KEY',        'TofoIvIDRyEauPa/IgMtC_,HzS)C!x%i+-jBke@O} h&R@zh;a>Esz~;2k:v-kC1' );
define( 'AUTH_SALT',        ';^a0V}]nIDtJ`Ik+LqwN*3^}~|md;jnwtr|l9Lvy8_J a9KQxQ}ttVrE=jCJ.Sg#' );
define( 'SECURE_AUTH_SALT', 'xn_3Sbpm^HyNU*%y+)M{d9+<acCpCL)OaHR!Stx)k^LGE!i&B-M7-Cem7EP:z?.D' );
define( 'LOGGED_IN_SALT',   '_3MSU9-<0|d5w|n|p- g)V(biy*m!1xlR[R`YOo /LVNRi?b#6AQ8Bi=[Cg%hC,[' );
define( 'NONCE_SALT',       'D?J51P7UT+6+u1Y[O:{Nhe{[#mJ@{sxeuLm5E[6TT.8~Qglv2:maX#p.~~bP*3-|' );
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define('DISABLE_WP_CRON', true);
define('WP_MEMORY_LIMIT', '256M');

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
define( 'WP_DEBUG', false );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define('DOMAIN_CURRENT_SITE', 'elkheta.com');
