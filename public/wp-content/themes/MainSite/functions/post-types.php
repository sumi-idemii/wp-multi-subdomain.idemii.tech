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

