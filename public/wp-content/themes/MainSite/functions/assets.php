<?php

/**
 * 読み込みリソース管理
 */
function load_assets() {
    wp_register_style('main-style', WP_ASSETS_PATH . 'css/main.css', array(), WP_ASSETS_VERSION);
    wp_register_style('index-style', WP_ASSETS_PATH . 'css/index.css', array(), WP_ASSETS_VERSION);

    wp_register_script('vendor-js', WP_ASSETS_PATH . 'js/vendor.js', array(), WP_ASSETS_VERSION, false);
    wp_register_script('main-js', WP_ASSETS_PATH . 'js/main.js', array('vendor-js'), WP_ASSETS_VERSION, false);
    wp_register_script('index-js', WP_ASSETS_PATH . 'js/index.js', array(), WP_ASSETS_VERSION, false);
    
    wp_enqueue_style('main-style');
    wp_enqueue_style('index-style');
    wp_enqueue_script('vendor-js');
    wp_enqueue_script('main-js');
    wp_enqueue_script('index-js');
}

/**
 * 管理画面の記事編集画面専用のCSSとJavaScriptを読み込み
 */
function load_admin_editor_assets() {
    // 記事編集画面（post.php）または新規投稿画面（post-new.php）でのみ読み込み
    global $pagenow;
    if (in_array($pagenow, array('post.php', 'post-new.php'))) {
        // 管理画面用のCSSを登録・読み込み
        wp_register_style('admin-editor-style', WP_ASSETS_PATH . 'css/admin/editor.css', array(), WP_ASSETS_VERSION);
        wp_enqueue_style('admin-editor-style');
        
        // 管理画面用のJavaScriptを登録・読み込み
        wp_register_script('admin-editor-js', get_template_directory_uri() . '/assets_admin/js/editor.js', array('wp-blocks', 'wp-element', 'wp-editor'), WP_ASSETS_VERSION, true);
        wp_enqueue_script('admin-editor-js');
    }
}

/**
 * カスタムフォントのプリロード
 */
function preload_custom_fonts() {
    $text_font_url = esc_url(WP_ASSETS_PATH . 'font/NagoyaUniversity/NagoyaUniversity-Regular.otf');
    $icon_font_url_eot = esc_url(WP_ASSETS_PATH . 'font/icomoon/icomoon.eot');
    $icon_font_url_ttf = esc_url(WP_ASSETS_PATH . 'font/icomoon/icomoon.ttf');
    $icon_font_url_woff = esc_url(WP_ASSETS_PATH . 'font/icomoon/icomoon.woff');
    $icon_font_url_svg = esc_url(WP_ASSETS_PATH . 'font/icomoon/icomoon.svg');
    ?>
    <link rel="preload" href="<?= esc_url($text_font_url); ?>" as="font" type="font/otf" crossorigin>
    <style>
        @font-face {
            font-family: 'Nagoya University';
            src: url('<?= $text_font_url; ?>') format('opentype');
        }
        @font-face {
            font-family: "icomoon";
            src: url("<?= $icon_font_url_eot; ?>");
            src: url("<?= $icon_font_url_eot; ?>") format("embedded-opentype"), url("<?= $icon_font_url_ttf; ?>") format("truetype"), url("<?= $icon_font_url_woff; ?>") format("woff"), url("<?= $icon_font_url_svg; ?>") format("svg");
            font-weight: normal;
            font-style: normal;
            font-display: block;
        }
    </style>
    <?php
}

add_action('wp_enqueue_scripts', 'load_assets');
add_action('wp_head', 'preload_custom_fonts', 1);
add_action('admin_enqueue_scripts', 'load_admin_editor_assets');

add_filter('script_loader_tag', function($tag, $handle) {
    if ($handle === 'main-js' || $handle === 'vendor-js' || $handle === 'index-js') {
        // defer属性を追加
        return str_replace('<script ', '<script defer ', $tag);
    }
    return $tag;
}, 10, 2);
