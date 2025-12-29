<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
// if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
// 	/**
// 	 * Enqueues editor-style.css in the editors.
// 	 *
// 	 * @since Twenty Twenty-Five 1.0
// 	 *
// 	 * @return void
// 	 */
// 	function twentytwentyfive_editor_style() {
// 		add_editor_style( get_parent_theme_file_uri( 'assets/css/editor-style.css' ) );
// 	}
// endif;
// add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;


// 定数の宣言
define('WP_THEMES_PATH', get_template_directory()); // テーマのルートディレクトリ
define('WP_THEMES_FUNCTION_PATH', WP_THEMES_PATH . '/functions/'); // ファンクションファイル格納パス
define('PER_PAGE_COLLECTION', 5);
define('PER_PAGE_SDGS', 6);
define('PER_PAGE_RESEARCHERS', 12);
define('PER_PAGE_ARTICLES', 12);
define('PER_PAGE_EVENTS', 12);
define('PER_PAGE_SEARCH', 6);
define('PER_PAGE', -1);
define('PAGES_DATA_CACHE_TIME', 60 * 5); // ページデータのキャッシュ時間（5分）
// assets用定数の宣言（WordPressルートのアセットを参照する）
define('WP_ASSETS_PATH', get_site_url() . '/assets/');
define('WP_ASSETS_VERSION', '202509231727');
// TDK用定数の宣言
// 改修箇所
define('SITE_NAME', 'マルチサイトfunction_php固定記述');
define('SITE_DESCRIPTION', 'Where courageous minds shape tomorrow');
define('OG_IMAGE', get_site_url() . '/assets/img/common/ogp.png');

// デフォルトのページネーションを無効化と検索機能の調整
function custom_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_archive()) {
            $query->set('posts_per_page', PER_PAGE); // 1ページあたりの表示件数
        }
    }
}
add_action('pre_get_posts', 'custom_posts_per_page');

// ファンクションライブラリのロード
// 依存関係を考慮して明示的に読み込む
$required_files = [
    'post-types.php',    // 投稿タイプの定義（他のファイルで使用される）
    'taxonomy.php',    // 投稿タイプの定義（他のファイルで使用される）
];

// 指定した順序でファイルを読み込む
foreach ($required_files as $file) {
    $file_path = WP_THEMES_FUNCTION_PATH . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// 残りのPHPファイルを読み込む
$loaded_files = array_map('basename', glob(WP_THEMES_FUNCTION_PATH . '*.php'));
$remaining_files = array_diff($loaded_files, $required_files);

foreach ($remaining_files as $file) {
    $file_path = WP_THEMES_FUNCTION_PATH . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}
