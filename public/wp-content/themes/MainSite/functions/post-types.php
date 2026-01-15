<?php
/**
 * デフォルトの投稿タイプを無効にする
 */
function disable_default_post_type() {
    remove_menu_page('edit.php'); // 投稿メニューを非表示にする
    unregister_post_type('post'); // 投稿タイプを無効にする
}
add_action('admin_menu', 'disable_default_post_type');

/**
 * 投稿タイプキーに「_setting」を含む場合、投稿一覧と新規投稿のメニューを非表示にする
 * タクソノミーのメニューのみ表示する
 */
function hide_setting_post_type_menus() {
    global $submenu;
    
    // 登録されているすべての投稿タイプを取得
    $post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
    
    foreach ($post_types as $post_type) {
        // 投稿タイプキーに「_setting」が含まれている場合
        if (strpos($post_type->name, '_setting') !== false) {
            $menu_slug = 'edit.php?post_type=' . $post_type->name;
            
            // サブメニューが存在する場合
            if (isset($submenu[$menu_slug])) {
                foreach ($submenu[$menu_slug] as $key => $menu_item) {
                    // 投稿一覧（親メニューと同じスラッグ）または新規投稿のサブメニューを非表示
                    if (isset($menu_item[2])) {
                        $submenu_slug = $menu_item[2];
                        if ($submenu_slug === $menu_slug || $submenu_slug === 'post-new.php?post_type=' . $post_type->name) {
                            unset($submenu[$menu_slug][$key]);
                        }
                    }
                }
            }
        }
    }
}
add_action('admin_menu', 'hide_setting_post_type_menus', 999);

/**
 * 左メニューの並び順をカスタマイズ
 * 順序: (1) ダッシュボード (2) 固定ページ (3) ACFの投稿タイプ一覧の投稿タイプ順 (4) 他、WordPressの標準メニュー
 */
function reorder_admin_menu_by_custom_order() {
    global $menu;
    
    // ACFの投稿タイプ画面の順序を取得
    // acf_get_post_types()を使用して、ACFの投稿タイプ画面と同じ順序を取得
    $acf_post_types = array();
    
    if (function_exists('acf_get_post_types')) {
        // ACFの投稿タイプを取得（ACFの投稿タイプ画面と同じ順序）
        $all_acf_post_types = acf_get_post_types();
        
        // 標準の投稿タイプ（post, page, attachment）を除外
        $excluded_types = array('post', 'page', 'attachment');
        
        foreach ($all_acf_post_types as $post_type) {
            if (!in_array($post_type, $excluded_types) && post_type_exists($post_type)) {
                $acf_post_types[] = $post_type;
            }
        }
    }
    
    // ACFから取得できない場合は、SCPOrderの順序を使用
    if (empty($acf_post_types)) {
        $scporder_options = get_option('scporder_options');
        
        if ($scporder_options && isset($scporder_options['objects']) && is_array($scporder_options['objects'])) {
            $found_acf_post_type = false;
            
            foreach ($scporder_options['objects'] as $post_type) {
                if ($post_type === 'acf-post-type') {
                    $found_acf_post_type = true;
                    continue;
                }
                
                if ($found_acf_post_type) {
                    if ($post_type === 'acf-field-group') {
                        break;
                    }
                    
                    if ($post_type !== 'acf-taxonomy' && post_type_exists($post_type)) {
                        $acf_post_types[] = $post_type;
                    }
                }
            }
        }
    }
    
    // それでも取得できない場合は、登録されているカスタム投稿タイプを取得
    if (empty($acf_post_types)) {
        $all_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
        foreach ($all_post_types as $post_type) {
            $acf_post_types[] = $post_type->name;
        }
    }
    
    // メニュー配列を再構築
    $ordered_menu = array();
    $dashboard_menu = null;
    $page_menu = null;
    $top_news_menu = null; // トップページニュース管理メニュー
    $acf_post_type_menus = array();
    $organisation_setting_menu = null; // 組織設定メニュー
    $other_menus = array();
    
    // 既存のメニューを分類
    foreach ($menu as $key => $menu_item) {
        if (isset($menu_item[2])) {
            $menu_slug = $menu_item[2];
            
            // (1) ダッシュボード
            if ($menu_slug === 'index.php') {
                $dashboard_menu = $menu_item;
                continue;
            }
            
            // (2) 固定ページ
            if ($menu_slug === 'edit.php?post_type=page') {
                $page_menu = $menu_item;
                continue;
            }
            
            // サイト設定メニューを除外
            if ($menu_slug === 'theme-general-settings') {
                continue;
            }
            
            // トップページニュース管理メニューを取得
            // 親メニュー（top-page-news-management）のみを取得
            if ($menu_slug === 'top-page-news-management') {
                $top_news_menu = $menu_item;
                continue;
            }
            
            // サブメニューは除外（親メニューのみを処理）
            if (strpos($menu_slug, 'page-category-news-') === 0 || 
                $menu_slug === 'top-page-announcements-management' ||
                $menu_slug === 'top-page-events-management') {
                continue;
            }
            
            // 組織設定メニューを取得（メニュータイトルまたは投稿タイプ名で判定）
            if (strpos($menu_slug, 'edit.php?post_type=') === 0) {
                $post_type = str_replace('edit.php?post_type=', '', $menu_slug);
                
                // メニュータイトルで「組織設定」を検索
                $menu_title = isset($menu_item[0]) ? $menu_item[0] : '';
                if (strpos($menu_title, '組織設定') !== false) {
                    $organisation_setting_menu = $menu_item;
                    continue;
                }
                
                // 組織設定関連の投稿タイプかチェック
                if (strpos($post_type, 'organisation') !== false || strpos($post_type, 'organization') !== false) {
                    $organisation_setting_menu = $menu_item;
                    continue;
                }
                
                // ACFの投稿タイプの順序に含まれている投稿タイプかチェック
                if (in_array($post_type, $acf_post_types)) {
                    // ACFの投稿タイプの順序に従って配列に追加
                    $acf_post_type_menus[] = array(
                        'order' => array_search($post_type, $acf_post_types),
                        'menu' => $menu_item
                    );
                    continue;
                }
            }
            
            // (4) その他のメニュー
            $other_menus[] = $menu_item;
        } else {
            $other_menus[] = $menu_item;
        }
    }
    
    // カスタム投稿タイプのメニューをACFの投稿タイプの順序でソート
    usort($acf_post_type_menus, function($a, $b) {
        return $a['order'] - $b['order'];
    });
    
    // メニューを指定の順序で再構築
    // (1) ダッシュボード
    if ($dashboard_menu !== null) {
        $ordered_menu[] = $dashboard_menu;
    }
    
    // (2) 固定ページ
    if ($page_menu !== null) {
        $ordered_menu[] = $page_menu;
    }
    
    // (3) トップページニュース管理（固定ページの下）
    if ($top_news_menu !== null) {
        $ordered_menu[] = $top_news_menu;
    }
    
    // (4) ACFの投稿タイプのメニューをACFの投稿タイプの順序で追加
    foreach ($acf_post_type_menus as $item) {
        $ordered_menu[] = $item['menu'];
    }
    
    // (5) その他のメニュー
    foreach ($other_menus as $menu_item) {
        $ordered_menu[] = $menu_item;
    }
    
    // メニューを更新
    $menu = $ordered_menu;
}
add_action('admin_menu', 'reorder_admin_menu_by_custom_order', 1000);

/**
 * related_site投稿タイプの一覧ページ・詳細ページをトップページにリダイレクト
 * 投稿の一覧ページ・詳細ページを持たない投稿タイプのリダイレクト処理
 */
function redirect_related_site_pages() {
    // 管理画面では実行しない
    if (is_admin()) {
        return;
    }
    
    // related_site投稿タイプのアーカイブページまたは詳細ページの場合
    if (is_post_type_archive('related_site') || is_singular('related_site')) {
        wp_redirect(home_url(), 301);
        exit;
    }
}
add_action('template_redirect', 'redirect_related_site_pages', 1);

