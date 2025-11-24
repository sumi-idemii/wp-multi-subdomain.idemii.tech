<?php
/**
 * MainSite Theme Functions
 *
 * @package MainSite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme setup
 */
function mainsite_setup() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );
}
add_action( 'after_setup_theme', 'mainsite_setup' );

/**
 * Enqueue scripts and styles
 */
function mainsite_scripts() {
	wp_enqueue_style( 'mainsite-style', get_stylesheet_uri(), array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'mainsite_scripts' );

