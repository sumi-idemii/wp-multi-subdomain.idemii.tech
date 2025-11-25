<?php

/**
 * ChildSite child theme functions.
 *
 * This theme extends the MainSite theme and only needs to enqueue the parent
 * stylesheet plus optional overrides that live in this child theme.
 */

if ( ! function_exists( 'childsite_enqueue_styles' ) ) {
	/**
	 * Enqueue parent and child styles.
	 */
	function childsite_enqueue_styles() {
		$parent_handle = 'mainsite-style';
		$parent_theme  = wp_get_theme( 'MainSite' );

		// 親テーマのスタイルを読み込む（親テーマが存在する場合）
		if ( $parent_theme->exists() ) {
			wp_enqueue_style(
				$parent_handle,
				get_template_directory_uri() . '/style.css',
				array(),
				$parent_theme->get( 'Version' )
			);
		}

		// 子テーマのスタイルシート
		wp_enqueue_style(
			'childsite-style',
			get_stylesheet_uri(),
			$parent_theme->exists() ? array( $parent_handle ) : array(),
			wp_get_theme()->get( 'Version' )
		);

		// assets/css/main.css を読み込む
		$main_css_path = get_stylesheet_directory() . '/assets/css/main.css';
		$main_css_url  = get_stylesheet_directory_uri() . '/assets/css/main.css';
		
		// ファイルが存在するか確認
		if ( file_exists( $main_css_path ) ) {
			wp_enqueue_style(
				'childsite-main-css',
				$main_css_url,
				array(), // 依存関係を削除して確実に読み込む
				filemtime( $main_css_path ) // ファイルの更新時刻をバージョンとして使用
			);
		}

		// assets/css/index.css を読み込む
		$index_css_path = get_stylesheet_directory() . '/assets/css/index.css';
		if ( file_exists( $index_css_path ) ) {
			wp_enqueue_style(
				'childsite-index-css',
				get_stylesheet_directory_uri() . '/assets/css/index.css',
				array( 'childsite-style' ),
				filemtime( $index_css_path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'childsite_enqueue_styles' );

if ( ! function_exists( 'childsite_enqueue_scripts' ) ) {
	/**
	 * Enqueue child theme scripts.
	 */
	function childsite_enqueue_scripts() {
		// assets/js/vendor.js を読み込む（依存関係として先に読み込む）
		wp_enqueue_script(
			'childsite-vendor-js',
			get_stylesheet_directory_uri() . '/assets/js/vendor.js',
			array( 'jquery' ),
			wp_get_theme()->get( 'Version' ),
			true // フッターで読み込む
		);

		// assets/js/main.js を読み込む
		wp_enqueue_script(
			'childsite-main-js',
			get_stylesheet_directory_uri() . '/assets/js/main.js',
			array( 'jquery', 'childsite-vendor-js' ),
			wp_get_theme()->get( 'Version' ),
			true // フッターで読み込む
		);

		// assets/js/index.js を読み込む
		wp_enqueue_script(
			'childsite-index-js',
			get_stylesheet_directory_uri() . '/assets/js/index.js',
			array( 'jquery', 'childsite-vendor-js' ),
			wp_get_theme()->get( 'Version' ),
			true // フッターで読み込む
		);
	}
}
add_action( 'wp_enqueue_scripts', 'childsite_enqueue_scripts' );

if ( ! function_exists( 'childsite_enqueue_admin_styles' ) ) {
	/**
	 * Enqueue admin styles for editor.
	 */
	function childsite_enqueue_admin_styles() {
		// assets/css/admin/editor.css を管理画面で読み込む
		wp_enqueue_style(
			'childsite-editor-css',
			get_stylesheet_directory_uri() . '/assets/css/admin/editor.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'childsite_enqueue_admin_styles' );