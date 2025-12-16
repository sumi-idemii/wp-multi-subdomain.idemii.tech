<?php
/**
 * 管理画面メニュー
 */

// アイキャッチ画像の有効化
add_theme_support( 'post-thumbnails' );

/**
 * ACFオプションページの追加
 * ACFプラグインが読み込まれた後に実行されるように複数のアクションフックを使用
 */
function add_acf_options_page() {
    // 既に実行された場合はスキップ（重複防止）
    static $executed = false;
    if ($executed) {
        return;
    }
    
    // ACFプラグインが有効化されているか確認
    if( !function_exists('get_field') ) {
        return;
    }
    
    // acf_add_options_page関数が存在する場合（ACF PRO版など）
    if( function_exists('acf_add_options_page') ) {
        acf_add_options_page(array(
            'page_title'    => 'サイト全体設定',
            'menu_title'    => 'サイト設定',
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'manage_options',
            'icon_url'      => 'dashicons-admin-settings',
            'redirect'      => false
        ));
        $executed = true;
        return;
    }
    
    // acf_add_options_page関数が存在しない場合、WordPress標準のオプションページを使用
    // ACFのオプションページ機能はACF PRO版でのみ利用可能な場合があります
    // メニューが既に存在するかチェック
    global $menu;
    $menu_exists = false;
    if (is_array($menu)) {
        foreach ($menu as $menu_item) {
            if (isset($menu_item[2]) && $menu_item[2] === 'theme-general-settings') {
                $menu_exists = true;
                break;
            }
        }
    }
    
    if (!$menu_exists) {
        add_menu_page(
            'サイト全体設定',           // ページタイトル
            'サイト設定',               // メニュータイトル
            'manage_options',           // 権限
            'theme-general-settings',   // メニュースラッグ
            'render_acf_options_page', // コールバック関数
            'dashicons-admin-settings', // アイコン
            30                          // 位置
        );
        $executed = true;
    }
}

/**
 * ACFオプションページのレンダリング
 * ACFフィールドグループを表示する
 */
function render_acf_options_page() {
    if (!current_user_can('manage_options')) {
        wp_die('このページにアクセスする権限がありません。');
    }
    
    // ACFが利用可能な場合、ACFのオプションページ機能を使用
    if (function_exists('acf_form')) {
        // ACFのオプションページとして表示
        ?>
        <div class="wrap">
            <h1>サイト全体設定</h1>
            <?php
            // ACFフォームを表示（オプションページ用）
            if (function_exists('acf_form')) {
                acf_form(array(
                    'post_id' => 'options',
                    'post_title' => false,
                    'post_content' => false,
                    'submit_value' => '設定を保存',
                    'updated_message' => '設定を保存しました。',
                ));
            }
            ?>
        </div>
        <?php
    } else {
        // ACFが利用できない場合のフォールバック
        ?>
        <div class="wrap">
            <h1>サイト全体設定</h1>
            <p>ACFプラグインが有効化されていないか、ACF PRO版が必要です。</p>
        </div>
        <?php
    }
}

// 管理メニューが構築される時に実行
add_action('admin_menu', 'add_acf_options_page', 20);

// フォールバック: ACFプラグインが初期化された後に実行
add_action('acf/init', 'add_acf_options_page', 20);