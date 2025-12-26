<?php
/**
 * REST API - News投稿タイプ関連の処理
 */

/**
 * マルチサイトとサブサイトのニュース記事一覧を取得するREST APIエンドポイント
 * 各記事の内容：URL、タイトル、キャッチ画像、ページカテゴリ（page_category）、組織（organisation）
 */
function register_multisite_news_api_endpoint() {
    register_rest_route('wp/v2', '/multisite-news', array(
        'methods' => 'GET',
        'callback' => 'get_multisite_news_list',
        'permission_callback' => '__return_true', // 公開エンドポイント
    ));
}
add_action('rest_api_init', 'register_multisite_news_api_endpoint');

/**
 * マルチサイトとサブサイトのニュース記事一覧を取得
 */
function get_multisite_news_list($request) {
    $news_list = array();
    
    // マルチサイトが有効な場合
    if (is_multisite()) {
        // すべてのサイトを取得
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // 現在のサイトのニュース記事を取得
            $site_news = get_current_site_news();
            
            // サイト情報を追加
            foreach ($site_news as $news) {
                $news['site_id'] = $site->blog_id;
                $news['site_url'] = get_site_url($site->blog_id);
                $news['site_name'] = get_bloginfo('name');
                $news_list[] = $news;
            }
            
            restore_current_blog();
        }
    } else {
        // シングルサイトの場合
        $news_list = get_current_site_news();
    }
    
    // 日付でソート（新しい順）
    usort($news_list, function($a, $b) {
        $date_a = isset($a['date']) ? strtotime($a['date']) : 0;
        $date_b = isset($b['date']) ? strtotime($b['date']) : 0;
        return $date_b - $date_a;
    });
    
    return new WP_REST_Response($news_list, 200);
}

/**
 * 現在のサイトのニュース記事を取得
 */
function get_current_site_news() {
    $news_list = array();
    
    // ニュース記事を取得
    $args = array(
        'post_type' => 'news',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $news_item = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'url' => get_permalink(),
                'date' => get_the_date('c'),
                'featured_image' => null,
                'page_category' => array(),
                'organisation' => array(),
            );
            
            // キャッチ画像を取得
            if (has_post_thumbnail($post_id)) {
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
                $news_item['featured_image'] = array(
                    'id' => $thumbnail_id,
                    'url' => $thumbnail_url,
                );
            }
            
            // ページカテゴリ（page_category）を取得
            if (taxonomy_exists('page_category')) {
                $page_categories = get_the_terms($post_id, 'page_category');
                if ($page_categories && !is_wp_error($page_categories)) {
                    foreach ($page_categories as $category) {
                        $news_item['page_category'][] = array(
                            'id' => $category->term_id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        );
                    }
                }
            }
            
            // 組織（organisation）を取得
            if (function_exists('get_field')) {
                $organisation_field = get_field('organisation', $post_id);
                if ($organisation_field) {
                    if (is_array($organisation_field)) {
                        foreach ($organisation_field as $org) {
                            if (is_object($org) && ($org instanceof WP_Term || get_class($org) === 'WP_Term')) {
                                $news_item['organisation'][] = array(
                                    'id' => $org->term_id,
                                    'name' => $org->name,
                                    'slug' => $org->slug,
                                );
                            } elseif (is_numeric($org)) {
                                $term = get_term($org, 'organisation');
                                if ($term && !is_wp_error($term)) {
                                    $news_item['organisation'][] = array(
                                        'id' => $term->term_id,
                                        'name' => $term->name,
                                        'slug' => $term->slug,
                                    );
                                }
                            }
                        }
                    } elseif (is_object($organisation_field) && ($organisation_field instanceof WP_Term || get_class($organisation_field) === 'WP_Term')) {
                        $news_item['organisation'][] = array(
                            'id' => $organisation_field->term_id,
                            'name' => $organisation_field->name,
                            'slug' => $organisation_field->slug,
                        );
                    } elseif (is_numeric($organisation_field)) {
                        $term = get_term($organisation_field, 'organisation');
                        if ($term && !is_wp_error($term)) {
                            $news_item['organisation'][] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'slug' => $term->slug,
                            );
                        }
                    }
                }
            }
            
            $news_list[] = $news_item;
        }
        wp_reset_postdata();
    }
    
    return $news_list;
}

/**
 * JSONファイルとしてニュース記事一覧を出力するエンドポイント
 */
function register_news_json_file_endpoint() {
    register_rest_route('wp/v2', '/multisite-news/json', array(
        'methods' => 'GET',
        'callback' => 'get_multisite_news_json_file',
        'permission_callback' => '__return_true', // 公開エンドポイント
    ));
}
add_action('rest_api_init', 'register_news_json_file_endpoint');

/**
 * JSONファイルとしてニュース記事一覧を出力
 */
function get_multisite_news_json_file($request) {
    // ニュース記事一覧を取得
    $news_list_response = get_multisite_news_list($request);
    $news_list = $news_list_response->get_data();
    
    // JSONファイルとして出力
    header('Content-Type: application/json; charset=' . get_option('blog_charset'));
    header('Content-Disposition: attachment; filename="multisite-news.json"');
    
    echo wp_json_encode($news_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

