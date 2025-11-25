<?php
/**
 * ACF投稿オブジェクトフィールドの表示をカスタマイズ
 * タイトルとslugを併記して表示する
 */
function custom_acf_post_object_result($title, $post, $field) {
    // related_researchersフィールドのみに適用
    if ($field['name'] === 'related_researchers') {
        $slug = $post->post_name;
        $title .= ' (' . $slug . ')';
    }
    return $title;
}
add_filter('acf/fields/post_object/result', 'custom_acf_post_object_result', 10, 4);
