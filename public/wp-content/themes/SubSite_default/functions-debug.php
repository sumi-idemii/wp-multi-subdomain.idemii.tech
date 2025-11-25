<?php
/**
 * デバッグ用: アセットの読み込み状況を確認
 * 
 * このファイルを一時的にfunctions.phpに追加して、読み込み状況を確認できます
 */

// デバッグモードが有効な場合のみ実行
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	add_action( 'wp_footer', function() {
		if ( current_user_can( 'administrator' ) ) {
			$main_css_path = get_stylesheet_directory() . '/assets/css/main.css';
			$main_css_url  = get_stylesheet_directory_uri() . '/assets/css/main.css';
			
			echo '<!-- SubSite_default Debug Info -->';
			echo '<div style="position:fixed;bottom:0;right:0;background:#000;color:#fff;padding:10px;z-index:9999;font-size:12px;">';
			echo '<strong>SubSite_default Theme Debug:</strong><br>';
			echo 'Stylesheet Directory: ' . get_stylesheet_directory() . '<br>';
			echo 'Stylesheet URI: ' . get_stylesheet_directory_uri() . '<br>';
			echo 'Main CSS Path: ' . $main_css_path . '<br>';
			echo 'Main CSS URL: ' . $main_css_url . '<br>';
			echo 'File Exists: ' . ( file_exists( $main_css_path ) ? 'YES' : 'NO' ) . '<br>';
			echo 'Current Theme: ' . get_stylesheet() . '<br>';
			echo '</div>';
		}
	} );
}

