<?php
/**
 * wp-cron用のファイル出力処理
 */

/**
 * ページデータを取得する関数
 * 
 * @return array ページデータ
 */
function get_cached_pages_data() {
    // キャッシュキーを生成
    $cache_key = 'pages_data';

    // 取得するページのスラッグ一覧
    $slugs = array('news', 'admissions', 'academics', 'research', 'campus', 'about', 'test');
    
    // キャッシュからデータを取得してあればそれを返す
    $cached_data = wp_cache_get($cache_key, 'nagoya_u_pages');
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // キャッシュにない場合はwpからデータを取得
    // 第1階層のページを取得
    $top_level_pages = get_pages(array(
        'sort_column' => 'menu_order', // 並び替えにメニュー順を使用
        'sort_order' => 'asc', // 昇順に並び替え
        'exclude' => get_option('page_on_front'),
        'parent' => 0
    ));
    $pages_data = array();
    
    foreach ($top_level_pages as $top_page) {
        $top_page_data = array(
            'id' => $top_page->ID,
            'title' => $top_page->post_title,
            'url' => get_permalink($top_page->ID),
            'slug' => $top_page->post_name,
            'lead' => get_post_meta($top_page->ID, 'common_lead', true),
            'children' => array()
        );

        // 第2階層のページを取得
        $second_level_pages = get_pages(array(
            'sort_column' => 'menu_order',
            'parent' => $top_page->ID
        ));
        
        if (!empty($second_level_pages)) {
            foreach ($second_level_pages as $second_page) {
                $second_page_data = array(
                    'id' => $second_page->ID,
                    'title' => $second_page->post_title,
                    'url' => get_permalink($second_page->ID),
                );
                
                $top_page_data['children'][] = $second_page_data;
            }
        }
        
        $pages_data[] = $top_page_data;
    }

    // 必要なデータのみに整形
    $result = array(); // $resultを初期化
    foreach ($slugs as $slug) {
        $filtered = array_filter($pages_data, function($page) use ($slug) {
            return isset($page['slug']) && $page['slug'] === $slug;
        });
        $result[$slug] = reset($filtered); // 見つかった場合は配列の最初の要素、見つからない場合はfalse
    }
    //改修箇所　下層ナビゲーションの表示？
    // newsが存在する場合のみchildrenを設定
    /*
    if (!empty($result['news'])) {
        $result['news']['children'] = array(
        array(  
            'title' => 'News',
            'url' => '/news/articles/',  
        ),
        array(
            'title' => 'Events',
            'url' => '/news/events/',
        ),
        array(
            'title' => 'Collection',
            'url' => '/news/collection/',
        ),
        array(
            'title' => 'Researchers',
            'url' => '/news/researchers/',
        ),
        array(
            'title' => 'Jobs',
            'url' => '/news/jobs/',
        ),
        );
    }
        */

    // キャッシュに保存
    //改修箇所
    wp_cache_set($cache_key, $result, 'nagoya_u_pages', PAGES_DATA_CACHE_TIME);
 
    return $result;
}
