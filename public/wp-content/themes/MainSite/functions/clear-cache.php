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
    
    // Transientをクリア（オプション）
    // clear_all_transients();
    
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
 * 管理画面にキャッシュクリアボタンを追加（オプション）
 */
if (is_admin()) {
    add_action('admin_bar_menu', function($wp_admin_bar) {
        $wp_admin_bar->add_menu(array(
            'id' => 'clear-cache',
            'title' => 'キャッシュクリア',
            'href' => admin_url('admin.php?action=clear_cache'),
        ));
    }, 100);
    
    add_action('admin_action_clear_cache', function() {
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません');
        }
        
        clear_all_wordpress_cache();
        
        wp_redirect(admin_url('admin.php?page=clear-cache&cleared=1'));
        exit;
    });
}

