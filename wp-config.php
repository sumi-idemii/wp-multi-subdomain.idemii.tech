<?php
/**
 * The base configuration for WordPress
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 * * Multisite configuration
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// Load environment variables from .env file if it exists
if ( file_exists( __DIR__ . '/.env' ) ) {
	$env_file = file( __DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	foreach ( $env_file as $line ) {
		// Skip comments
		if ( strpos( trim( $line ), '#' ) === 0 ) {
			continue;
		}
		// Parse KEY=VALUE format
		if ( strpos( $line, '=' ) !== false ) {
			list( $key, $value ) = explode( '=', $line, 2 );
			$key = trim( $key );
			$value = trim( $value );
			// Remove quotes if present
			$value = trim( $value, '"\'');
			if ( ! getenv( $key ) ) {
				putenv( "$key=$value" );
				$_ENV[ $key ] = $value;
				$_SERVER[ $key ] = $value;
			}
		}
	}
}

// Helper function to get environment variable with fallback
function wp_get_env( $key, $default = '' ) {
	$value = getenv( $key );
	if ( $value === false ) {
		return $default;
	}
	return $value;
}

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', wp_get_env( 'DB_NAME', 'database_name_here' ) );

/** Database username */
define( 'DB_USER', wp_get_env( 'DB_USER', 'username_here' ) );

/** Database password */
define( 'DB_PASSWORD', wp_get_env( 'DB_PASSWORD', 'password_here' ) );

/** Database hostname */
// Use 127.0.0.1 for Docker MySQL TCP connection instead of localhost (socket)
define( 'DB_HOST', wp_get_env( 'DB_HOST', '127.0.0.1' ) );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', wp_get_env( 'DB_CHARSET', 'utf8mb4' ) );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', wp_get_env( 'DB_COLLATE', 'utf8mb4_unicode_ci' ) );

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
define('AUTH_KEY',         'Jum;M.Tq+}0=kO7(L<JOFPM4DH8#{<sGC-b?=}!;B1ODx<t3iU$+.?Au9:GwSjy<');
define('SECURE_AUTH_KEY',  '~,/MQ*tr/g#jmiyzT77^uxQbn%g3fwqdAGu =}Jv#-|a<=jn:blGlcXeT%i8URV:');
define('LOGGED_IN_KEY',    'f_-A(Et+@(ID{Jil)vm:+9n(Zhz(z@@3_!+o][_g_cStc9f/^-,$-P$Al5-x%;W)');
define('NONCE_KEY',        'G?Gl%Be9},kDZb]~D5+>-Drc>_?mLM;`c3mQDSm=1UZKw8sDQV*5WK+T)@&=?Dwv');
define('AUTH_SALT',        'Tn|iS-4)ZU+7S>KpmTm?Q#r B,dwzCQg{^$dh_7My{1T;&mFV1|tnVi<K]UbA[}e');
define('SECURE_AUTH_SALT', '~]GoCc!}@ugBrlo$x>c /acJzs<?`9qv{EJ*0a.HLs1[Fy>Fw{P|rxAeK0Zl]00H');
define('LOGGED_IN_SALT',   'AFAnAB_;1Bbf5+fWx#a/VQ~&g DPhb:Rm%$ava<?dwq+Fv7=?r6+-ZGVbEg0GmX9');
define('NONCE_SALT',       ':4C8A>!y(m)jY(&t#lSqbb,wS|9Wfx`}dWx?Fu$)?Vg#c/Q+MAD+*TFXz-0CgA :');

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

/**
 * Multisite Configuration
 * 
 * Enable WordPress Multisite functionality
 * After enabling this, you need to run the WordPress installation
 * and then configure multisite through the admin panel.
 */
define( 'WP_ALLOW_MULTISITE', true );

/**
 * Multisite Domain Mapping
 * Set to true if you want to use domain mapping for multisite
 */
define( 'SUNRISE', false );

/**
 * File Upload Settings for Multisite
 * Set to true to allow file uploads for all sites in the network
 */
define( 'MULTISITE', false ); // This will be set to true after multisite setup

/**
 * Subdomain Configuration
 * Set to true for subdomain-based multisite (e.g., site1.example.com)
 * Set to false for subdirectory-based multisite (e.g., example.com/site1)
 * Note: This will be configured during the multisite setup process
 */
// define( 'SUBDOMAIN_INSTALL', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

