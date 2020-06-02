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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Online' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         ']{s*RWYeZx<a1[;rxR:r5S<dlWrRA09MDy;$pK/X>Vu{?ZzHVz1|gLI?7TsE4y2-' );
define( 'SECURE_AUTH_KEY',  'D+7X&c{N4nH<s*`&BserwO$r%6t7^Yb^H1j^sD7Oh+m+24Sv{yT}C`%CXTZO}5Te' );
define( 'LOGGED_IN_KEY',    ',Q#&lw5_v?2 L,SRP5RcUH*.do_trQZCW:e,2s&7;8*&:?`)5N[=q&2Ba^NJH+&%' );
define( 'NONCE_KEY',        'X4>UGTopybnO:)/bN|c.qxBEc0vL4y#BEWCYPT-_^}o6K+wf^n+%DaM*9)8CY_l8' );
define( 'AUTH_SALT',        '$)au[CC.8o<1cl}=o?f~JxFmP[`8Qe35<L?{4+]0)]AoIg:`E?^YHV{H/Ae)y,Yu' );
define( 'SECURE_AUTH_SALT', 'tT&!j2180(854pX{U{`qVN5@.2,h}Y-)(Z]=$jO(eS%C q|2U/5Z>[@o3xhvQ$%*' );
define( 'LOGGED_IN_SALT',   '</IESr1#o l`.7TV3mQUY/sVaxH9@91DA)yQH:Z;n-nKFZDL b7m4 OIMAb-x;l<' );
define( 'NONCE_SALT',       'oMD&8dd(q<F(QU{#_s17*AFr&%no##A<LLF(6#<(:Q+yl1v c_7IA=^j%G~8Vw$y' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
