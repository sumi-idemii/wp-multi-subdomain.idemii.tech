<?php

// 定数が定義されていない場合のデフォルト値を設定
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'マルチサイトfunction_php固定記述');
}
if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'Where courageous minds shape tomorrow');
}
if (!defined('OG_IMAGE')) {
    define('OG_IMAGE', get_site_url() . '/assets/img/common/ogp.png');
}

function get_meta() {
    // トップページの場合は特別な処理
    if (is_home() || is_front_page()) {
        $current_title = SITE_NAME;
        $current_url = get_site_url();
    } else {
        $current_title = get_the_title();
        $current_url = get_permalink();
    }

    $site_url = get_site_url();
    $current_path = str_replace($site_url, '', $current_url);

    // metaの設定
    $meta = array();
    $meta['title'] = SITE_NAME;
    $meta['description'] = SITE_DESCRIPTION;
    $meta['image'] = OG_IMAGE;
    $meta['site_name'] = SITE_NAME;
    $meta['path'] = $current_path;
    $meta['url'] = $current_url;

    // ページタイプの配列
    // 改修箇所 ページタイプの配列
    $page_types = ['news', 'admissions', 'academics', 'research', 'campus', 'about', 'site'];
    
    $page_type = '';
    foreach ($page_types as $type) {
        if (strpos($current_path, '/' . $type . '/') === 0) {
            $page_type = $type;
            break;
        }
    }

    // 検索ページの処理
    if (is_search()) {
        $meta['title'] = 'Search | ' . SITE_NAME;
        $meta['description'] = '検索結果';

    // アーカイブページの処理
    } else if (is_archive()) {
        // ニュースページトップの情報を取得
        $news_meta = get_page_by_path('news');

        if (is_post_type_archive()) {
            // 投稿タイプの情報を取得
            $post_type = get_post_type();
            $post_type_meta = get_post_type_object($post_type);
        } else {
            // タクソノミーの情報を取得
            $term = get_queried_object();
            $taxonomy = get_taxonomy($term->taxonomy);
            $post_type_meta = $taxonomy;
        }

        $meta['title'] = ' | ' . $news_meta->post_title . ' | ' . $meta['title'];

        if ($post_type_meta->name === 'page') {
            $meta['title'] = $current_title . $meta['title'];
        } else {
            $meta['title'] = $post_type_meta->labels->name . $meta['title'];
        }
        // ニュースページトップのdescriptionを取得して入れる
        $meta['description'] = get_post_meta($news_meta->ID, 'common_lead', true);

    } else if ($page_type) {
        // 固定ページのslugが$page_typeのものを取得
        $page = get_page_by_path($page_type);
        if ($page) {
            $meta['title'] = $page->post_title . ' | ' . SITE_NAME;
            $meta['description'] = get_post_meta($page->ID, 'common_lead', true);

            // 第二階層トップかどうか判定
            $is_second_level_top = ($current_path === '/' . $page_type . '/');
            if (!$is_second_level_top) {
                $meta['title'] = $current_title . ' | ' . $meta['title'];
            }
        }
    }

    // articles詳細の場合はサムネイルを取得
    if (is_single() && get_post_type() === 'articles') {
        $meta['image'] = get_the_post_thumbnail_url() ? get_the_post_thumbnail_url() : OG_IMAGE;
    }

    return $meta;
}
