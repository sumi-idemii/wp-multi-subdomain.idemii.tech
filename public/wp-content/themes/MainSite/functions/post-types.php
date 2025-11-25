<?php
/**
 * デフォルトの投稿タイプを無効にする
 */
function disable_default_post_type() {
    remove_menu_page('edit.php'); // 投稿メニューを非表示にする
    unregister_post_type('post'); // 投稿タイプを無効にする
}
add_action('admin_menu', 'disable_default_post_type');

