<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'savings');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '--`9VtY]S6`M+pQ1u-X!~+$aI[?`PPQ^R0358V?pnpO?7^ ^q#hyr`AOTm<lJ|pc');
define('SECURE_AUTH_KEY',  '-*MCk-c=6@x|38S9!b=`muIGt,9-:sX<8;O4/m-u>s}SE_Ghx;Zt6dC1((zjrfMt');
define('LOGGED_IN_KEY',    'P<fAB-f>x4_Tq(2bd;=b0e$P=Rb[zYw8]RAI9_.9^(; y553Pc+D8bk-]qG=;-4w');
define('NONCE_KEY',        'j$:o2y>dTmuSA;TTSG TZt]]>c +fh+_7vC)9u*:DndC*PR8=6G[Z;AB2&]Zz&n}');
define('AUTH_SALT',        'Uu>zh9tT:#K&$M(9q3=o/l@#jXU]N6r+HAP!?-@|*rQsYs0=6eZ}x`vO23gy3|wt');
define('SECURE_AUTH_SALT', 'S|=4m9-V|`bZ-l^gdn!HVr1:]*CnQqc%a79?^,:L*8A @B+38O.(hLoXR#J?d?As');
define('LOGGED_IN_SALT',   'jx6t|sx|/vKtda3Z;0b+6KSrI(H$/`~ M_qh^ky+>Soa+jL&H|C2xNJ2h#25QgBS');
define('NONCE_SALT',       '_ ^n=i[RV0ERpf|EL`uS9r@Btm|[;,NM**7EA,HPv^e$*eM5:52XEWbE,n.X.l9a');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
