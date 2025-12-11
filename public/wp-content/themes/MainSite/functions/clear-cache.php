<?php
/**
 * キャッシュクリア機能
 * WordPressの各種キャッシュをクリアする関数
 */

/**
 * すべてのキャッシュをクリア
 */
function clear_all_wordpress_cache() {
    // オブジェクトキャッシュをクリア
    wp_cache_flush();
    
    // リライトルールをフラッシュ
    flush_rewrite_rules();
    
    // Transientをクリア
    clear_all_transients();
    
    // REST APIキャッシュをクリア
    clear_rest_api_cache();
    
    return true;
}

/**
 * REST APIキャッシュをクリア
 */
function clear_rest_api_cache() {
    global $wpdb;
    
    // REST API関連のtransientを削除
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_rest_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_rest_%'");
    
    // オブジェクトキャッシュもクリア
    wp_cache_flush();
    
    return true;
}

/**
 * 特定のキャッシュグループをクリア
 * 
 * @param string $group キャッシュグループ名（例: 'nagoya_u_pages'）
 */
function clear_cache_group($group) {
    // キャッシュキーを削除
    wp_cache_delete('pages_data', $group);
    
    return true;
}

/**
 * すべてのTransientをクリア（注意: パフォーマンスに影響する可能性があります）
 */
function clear_all_transients() {
    global $wpdb;
    
    // サイト全体のtransientを削除
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    
    // マルチサイトの場合、各サイトのtransientも削除
    if (is_multisite()) {
        $wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_%'");
        
        $site_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        foreach ($site_ids as $site_id) {
            $table_name = $wpdb->get_blog_prefix($site_id) . 'options';
            $wpdb->query("DELETE FROM {$table_name} WHERE option_name LIKE '_transient_%'");
        }
    }
    
    return true;
}

/**
 * リライトルールのみをクリア
 */
function clear_rewrite_rules() {
    // リライトルールをデータベースから削除
    delete_option('rewrite_rules');
    
    // リライトルールを再生成
    flush_rewrite_rules();
    
    return true;
}

/**
 * 管理画面にキャッシュクリアボタンを追加
 */
if (is_admin()) {
    // 管理バーにキャッシュクリアメニューを追加
    add_action('admin_bar_menu', function($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $wp_admin_bar->add_menu(array(
            'id' => 'clear-cache',
            'title' => 'キャッシュクリア',
            'href' => '#',
        ));
        
        $wp_admin_bar->add_menu(array(
            'parent' => 'clear-cache',
            'id' => 'clear-all-cache',
            'title' => 'すべてのキャッシュをクリア',
            'href' => wp_nonce_url(admin_url('admin-post.php?action=clear_all_cache'), 'clear_all_cache_nonce'),
        ));
        
        $wp_admin_bar->add_menu(array(
            'parent' => 'clear-cache',
            'id' => 'clear-rest-api-cache',
            'title' => 'REST APIキャッシュをクリア',
            'href' => wp_nonce_url(admin_url('admin-post.php?action=clear_rest_api_cache'), 'clear_rest_api_cache_nonce'),
        ));
    }, 100);
    
    // すべてのキャッシュをクリア
    add_action('admin_post_clear_all_cache', function() {
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません');
        }
        
        check_admin_referer('clear_all_cache_nonce');
        
        clear_all_wordpress_cache();
        
        wp_redirect(add_query_arg('cache_cleared', 'all', admin_url()));
        exit;
    });
    
    // REST APIキャッシュのみをクリア
    add_action('admin_post_clear_rest_api_cache', function() {
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません');
        }
        
        check_admin_referer('clear_rest_api_cache_nonce');
        
        clear_rest_api_cache();
        
        wp_redirect(add_query_arg('cache_cleared', 'rest_api', admin_url()));
        exit;
    });
    
    // キャッシュクリア成功メッセージを表示
    add_action('admin_notices', function() {
        if (isset($_GET['cache_cleared'])) {
            $type = $_GET['cache_cleared'] === 'rest_api' ? 'REST API' : 'すべての';
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($type . 'キャッシュをクリアしました。') . '</p></div>';
        }
    });
}


