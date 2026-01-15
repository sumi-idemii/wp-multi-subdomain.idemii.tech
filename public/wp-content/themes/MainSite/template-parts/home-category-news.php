<?php
/**
 * Template part for displaying category news section
 * top_category_areaのpage_categoryタクソノミーに基づいてニュース記事を表示
 * 該当タクソノミーのスラッグにマッチする「トップページニュース管理」でチェックがONのニュース記事を表示
 *
 * @package wp-multi-subdomain.idemii.tech
 * @param array $args {
 *     @type array $page_category_ids page_categoryタクソノミーのID配列
 * }
 */

$page_category_ids = isset($args['page_category_ids']) ? $args['page_category_ids'] : array();

// page_categoryタクソノミーが設定されていない場合は何も表示しない
if (empty($page_category_ids)) {
    return;
}

// タクソノミーのIDからスラッグを取得
$page_category_slugs = array();
foreach ($page_category_ids as $term_id) {
    $term = get_term($term_id, 'page_category');
    if ($term && !is_wp_error($term)) {
        $page_category_slugs[] = $term->slug;
    }
}

// スラッグが取得できない場合は何も表示しない
if (empty($page_category_slugs)) {
    return;
}

// チェックがONのニュース記事を取得（マルチサイト対応）
$news_posts = array();
$seen_post_ids = array(); // 重複チェック用（post_id_site_id形式）

// マルチサイト環境の場合
if (is_multisite()) {
    $sites = get_sites(array('number' => 0));
    
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        
        // 各スラッグごとにチェックがONの記事を取得
        foreach ($page_category_slugs as $term_slug) {
            // 該当スラッグにチェックがONのニュース記事を取得
            $args = array(
                'post_type' => 'news',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'page_category',
                        'field' => 'slug',
                        'terms' => $term_slug,
                    ),
                ),
                'meta_query' => array(
                    array(
                        'key' => '_show_on_top_page_' . $term_slug,
                        'value' => '1',
                        'compare' => '=',
                    ),
                ),
            );
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $post_key = $post_id . '_' . $site->blog_id;
                    
                    // 重複チェック
                    if (!isset($seen_post_ids[$post_key])) {
                        $seen_post_ids[$post_key] = true;
                        
                        $news_posts[] = array(
                            'id' => $post_id,
                            'title' => get_the_title(),
                            'date' => get_the_date('c'),
                            'date_timestamp' => get_the_date('U'),
                            'permalink' => get_permalink(),
                            'thumbnail_url' => has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'medium') : '',
                            'site_id' => $site->blog_id,
                        );
                    }
                }
                wp_reset_postdata();
            }
        }
        
        restore_current_blog();
    }
} else {
    // シングルサイトの場合
    foreach ($page_category_slugs as $term_slug) {
        // 該当スラッグにチェックがONのニュース記事を取得
        $args = array(
            'post_type' => 'news',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'page_category',
                    'field' => 'slug',
                    'terms' => $term_slug,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => '_show_on_top_page_' . $term_slug,
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_key = $post_id . '_1';
                
                // 重複チェック
                if (!isset($seen_post_ids[$post_key])) {
                    $seen_post_ids[$post_key] = true;
                    
                    $news_posts[] = array(
                        'id' => $post_id,
                        'title' => get_the_title(),
                        'date' => get_the_date('c'),
                        'date_timestamp' => get_the_date('U'),
                        'permalink' => get_permalink(),
                        'thumbnail_url' => has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'medium') : '',
                        'site_id' => 1,
                    );
                }
            }
            wp_reset_postdata();
        }
    }
}

// 日付でソート（新しい順）
usort($news_posts, function($a, $b) {
    $date_a = isset($a['date_timestamp']) ? $a['date_timestamp'] : 0;
    $date_b = isset($b['date_timestamp']) ? $b['date_timestamp'] : 0;
    return $date_b - $date_a;
});

// 最大8件に制限
$news_posts = array_slice($news_posts, 0, 8);

if (!empty($news_posts)) :
    ?>
    <section class="p-top-category-news">
        <h2 class="p-top-category-news-title">ニュース</h2>
        <ul class="p-top-category-news-list">
            <?php
            foreach ($news_posts as $post_data) :
                $post_id = $post_data['id'];
                $thumbnail_url = isset($post_data['thumbnail_url']) ? $post_data['thumbnail_url'] : '';
                $post_date = date('Y年m月d日', $post_data['date_timestamp']);
                
                // マルチサイトの場合、正しいサイトに切り替えてパーマリンクを取得
                if (is_multisite() && isset($post_data['site_id'])) {
                    switch_to_blog($post_data['site_id']);
                    $permalink = get_permalink($post_id);
                    restore_current_blog();
                } else {
                    $permalink = $post_data['permalink'];
                }
                ?>
                <li class="p-top-category-news-item">
                    <a href="<?php echo esc_url($permalink); ?>" class="p-top-category-news-link">
                        <?php if ($thumbnail_url) : ?>
                            <div class="p-top-category-news-thumbnail">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($post_data['title']); ?>" class="p-top-category-news-image">
                            </div>
                        <?php endif; ?>
                        <div class="p-top-category-news-content">
                            <h3 class="p-top-category-news-title-item">
                                <?php echo esc_html($post_data['title']); ?>
                            </h3>
                            <div class="p-top-category-news-meta">
                                <?php if (!empty($post_date)) : ?>
                                    <time class="p-top-category-news-date" datetime="<?php echo esc_attr($post_data['date']); ?>">
                                        <?php echo esc_html($post_date); ?>
                                    </time>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </li>
                <?php
            endforeach;
            ?>
        </ul>
    </section>
<?php endif; ?>


