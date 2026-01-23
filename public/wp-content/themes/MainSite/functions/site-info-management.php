<?php
/**
 * サイト情報管理機能
 * 
 * 【機能概要】
 * - フッターに表示するサイト運営者情報、お問い合わせ情報を管理画面で管理
 * - 日本語・英語の2言語に対応
 * 
 * 【削除方法】
 * このファイルを削除するだけで機能を無効化できます。
 * データベースに保存されたオプション値（site_info_*）は残りますが、
 * 機能が無効化されれば使用されません。
 * 
 * @package wp-multi-subdomain.idemii.tech
 */

/**
 * サイト情報管理の管理画面メニューを追加
 */
function add_site_info_management_menu() {
    add_menu_page(
        'サイト情報管理', // ページタイトル
        'サイト情報管理', // メニュータイトル
        'manage_options', // 権限
        'site-info-management', // メニュースラッグ
        'render_site_info_management_page', // コールバック関数
        'dashicons-admin-settings', // アイコン
        25 // 位置
    );
}
add_action('admin_menu', 'add_site_info_management_menu');

/**
 * サイト情報管理の保存処理（admin_initフックで実行）
 */
function handle_site_info_save() {
    // サイト情報管理ページでのみ処理
    if (!isset($_GET['page']) || $_GET['page'] !== 'site-info-management') {
        return;
    }
    
    // 保存処理
    if (isset($_POST['save_site_info']) && check_admin_referer('save_site_info_action')) {
        // サイト運営者情報
        update_option('site_info_operator_ja', isset($_POST['site_info_operator_ja']) ? wp_kses_post($_POST['site_info_operator_ja']) : '');
        update_option('site_info_operator_en', isset($_POST['site_info_operator_en']) ? wp_kses_post($_POST['site_info_operator_en']) : '');
        
        // お問い合わせ
        update_option('site_info_contact_ja', isset($_POST['site_info_contact_ja']) ? wp_kses_post($_POST['site_info_contact_ja']) : '');
        update_option('site_info_contact_en', isset($_POST['site_info_contact_en']) ? wp_kses_post($_POST['site_info_contact_en']) : '');
        
        // お問い合わせリンク先URL
        update_option('site_info_contact_url_ja', isset($_POST['site_info_contact_url_ja']) ? esc_url_raw($_POST['site_info_contact_url_ja']) : '');
        update_option('site_info_contact_url_en', isset($_POST['site_info_contact_url_en']) ? esc_url_raw($_POST['site_info_contact_url_en']) : '');
        
        // POST-Redirect-GETパターンでリダイレクト（保存後に再読み込みを防ぐ）
        wp_redirect(add_query_arg(array('page' => 'site-info-management', 'settings-updated' => 'true'), admin_url('admin.php')));
        exit;
    }
}
add_action('admin_init', 'handle_site_info_save');

/**
 * サイト情報管理の管理画面ページをレンダリング
 */
function render_site_info_management_page() {
    // 保存成功メッセージを表示
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>設定を保存しました。</p></div>';
    }
    
    // 現在の値を取得（デフォルト値は空文字列）
    $operator_ja = get_option('site_info_operator_ja', '');
    $operator_en = get_option('site_info_operator_en', '');
    $contact_ja = get_option('site_info_contact_ja', '');
    $contact_en = get_option('site_info_contact_en', '');
    $contact_url_ja = get_option('site_info_contact_url_ja', '');
    $contact_url_en = get_option('site_info_contact_url_en', '');
    
    ?>
    <div class="wrap">
        <h1>サイト情報管理</h1>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=site-info-management'); ?>">
            <?php wp_nonce_field('save_site_info_action'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="site_info_operator_ja">サイト運営者情報[日本語]</label>
                    </th>
                    <td>
                        <textarea 
                            id="site_info_operator_ja" 
                            name="site_info_operator_ja" 
                            rows="5" 
                            cols="50" 
                            class="large-text"><?php echo esc_textarea($operator_ja); ?></textarea>
                        <p class="description">フッターに表示されるサイト運営者情報（日本語）を入力してください。<br>タグは使用可能です。</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="site_info_operator_en">サイト運営者情報[英語]</label>
                    </th>
                    <td>
                        <textarea 
                            id="site_info_operator_en" 
                            name="site_info_operator_en" 
                            rows="5" 
                            cols="50" 
                            class="large-text"><?php echo esc_textarea($operator_en); ?></textarea>
                        <p class="description">フッターに表示されるサイト運営者情報（英語）を入力してください。<br>タグは使用可能です。</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="site_info_contact_ja">お問い合わせ[日本語]</label>
                    </th>
                    <td>
                        <textarea 
                            id="site_info_contact_ja" 
                            name="site_info_contact_ja" 
                            rows="3" 
                            cols="50" 
                            class="large-text"><?php echo esc_textarea($contact_ja); ?></textarea>
                        <p class="description">フッターに表示されるお問い合わせテキスト（日本語）を入力してください。</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="site_info_contact_en">お問い合わせ[英語]</label>
                    </th>
                    <td>
                        <textarea 
                            id="site_info_contact_en" 
                            name="site_info_contact_en" 
                            rows="3" 
                            cols="50" 
                            class="large-text"><?php echo esc_textarea($contact_en); ?></textarea>
                        <p class="description">フッターに表示されるお問い合わせテキスト（英語）を入力してください。</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="site_info_contact_url_ja">お問い合わせリンク先URL[日本語]</label>
                    </th>
                    <td>
                        <input 
                            type="url" 
                            id="site_info_contact_url_ja" 
                            name="site_info_contact_url_ja" 
                            value="<?php echo esc_attr($contact_url_ja); ?>" 
                            class="regular-text" 
                            placeholder="https://example.com">
                        <p class="description">お問い合わせテキスト（日本語）のリンク先URLを入力してください。</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="site_info_contact_url_en">お問い合わせリンク先URL[英語]</label>
                    </th>
                    <td>
                        <input 
                            type="url" 
                            id="site_info_contact_url_en" 
                            name="site_info_contact_url_en" 
                            value="<?php echo esc_attr($contact_url_en); ?>" 
                            class="regular-text" 
                            placeholder="https://example.com">
                        <p class="description">お問い合わせテキスト（英語）のリンク先URLを入力してください。</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('設定を保存', 'primary', 'save_site_info'); ?>
        </form>
    </div>
    <?php
}

/**
 * 現在の言語に応じたサイト運営者情報を取得
 * 
 * @return string サイト運営者情報
 */
function get_site_info_operator() {
    $lang = get_current_language_code();
    
    if ($lang === 'en') {
        $operator = get_option('site_info_operator_en', '');
        // 英語が空の場合は日本語を返す
        if (empty($operator)) {
            $operator = get_option('site_info_operator_ja', '');
        }
    } else {
        $operator = get_option('site_info_operator_ja', '');
    }
    
    // 改行文字を<br>タグに変換
    if (!empty($operator)) {
        $operator = nl2br($operator, false);
    }
    
    return $operator;
}

/**
 * 現在の言語に応じたお問い合わせテキストを取得
 * 
 * @return string お問い合わせテキスト
 */
function get_site_info_contact() {
    $lang = get_current_language_code();
    
    if ($lang === 'en') {
        $contact = get_option('site_info_contact_en', '');
        // 英語が空の場合は日本語を返す
        if (empty($contact)) {
            $contact = get_option('site_info_contact_ja', '');
        }
    } else {
        $contact = get_option('site_info_contact_ja', '');
    }
    
    // 改行文字を<br>タグに変換
    if (!empty($contact)) {
        $contact = nl2br($contact, false);
    }
    
    return $contact;
}

/**
 * 現在の言語に応じたお問い合わせリンク先URLを取得
 * 
 * @return string お問い合わせリンク先URL
 */
function get_site_info_contact_url() {
    $lang = get_current_language_code();
    
    if ($lang === 'en') {
        $url = get_option('site_info_contact_url_en', '');
        // 英語が空の場合は日本語を返す
        if (empty($url)) {
            $url = get_option('site_info_contact_url_ja', '');
        }
    } else {
        $url = get_option('site_info_contact_url_ja', '');
    }
    
    return $url;
}

