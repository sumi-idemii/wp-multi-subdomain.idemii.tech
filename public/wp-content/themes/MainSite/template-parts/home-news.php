<?php
/**
 * Template part for displaying news section
 * 
 * 【機能説明】
 * page_categoryタクソノミーの各スラッグで「トップページ表示」にチェックがONのニュース記事を表示します。
 * この機能は page-category-news-management.php で管理されます。
 * 
 * @package wp-multi-subdomain.idemii.tech
 */

// チェックがONのニュース記事を取得（マルチサイト対応）
$news_posts = get_top_page_posts_by_type('news');

// ニュース記事が存在する場合のみ表示
if (!empty($news_posts)) :
    // 最大8件に制限
    $news_posts = array_slice($news_posts, 0, 8);
    
    // 記事データを直接使用（WP_Queryを使わない）
    $display_news_posts = $news_posts;
else :
    // チェックがONの記事がない場合、従来通り最新8件を取得
    $news_query = new WP_Query(array(
        'post_type'      => 'news',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    $display_news_posts = null; // WP_Queryを使用
endif;

// 表示するニュース記事がある場合
if (!empty($display_news_posts) || (isset($news_query) && $news_query->have_posts())) :
    ?>
    <section class="p-top-news">
        <h2 class="p-top-news-title">ニュース</h2>
        <ul class="p-top-news-list">
            <?php
            if (!empty($display_news_posts)) :
                // チェックがONの記事を表示（マルチサイト対応）
                foreach ($display_news_posts as $post_data) :
                    $post_id = $post_data['id'];
                    $post_title = $post_data['title'];
                    $thumbnail_url = '';
                    $post_permalink = '';
                    $post_date = '';
                    
                    // 該当サイトに切り替え
                    if (is_multisite() && isset($post_data['site_id'])) {
                        switch_to_blog($post_data['site_id']);
                    }
                    
                    // キャッチ画像を取得
                    if (has_post_thumbnail($post_id)) {
                        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    
                    // 日付を取得
                    $post_date = date('Y年m月d日', strtotime($post_data['date']));
                    $post_permalink = get_permalink($post_id);
                    
                    // マルチサイト環境の場合、元のサイトに戻す
                    if (is_multisite() && isset($post_data['site_id'])) {
                        restore_current_blog();
                    }
                    ?>
                    <li class="p-top-news-item">
                        <a href="<?php echo esc_url($post_permalink); ?>" class="p-top-news-link">
                            <?php if ($thumbnail_url) : ?>
                                <div class="p-top-news-thumbnail">
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($post_title); ?>" class="p-top-news-image">
                                </div>
                            <?php endif; ?>
                            <div class="p-top-news-content">
                                <h3 class="p-top-news-title-item">
                                    <?php echo esc_html($post_title); ?>
                                </h3>
                                <div class="p-top-news-meta">
                                    <?php if (!empty($post_date)) : ?>
                                        <time class="p-top-news-date" datetime="<?php echo esc_attr($post_data['date']); ?>">
                                            <?php echo esc_html($post_date); ?>
                                        </time>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php
                endforeach;
            else :
                // 従来通りWP_Queryを使用
                while ($news_query->have_posts()) :
                    $news_query->the_post();
                    $post_id = get_the_ID();
                    
                    // キャッチ画像を取得
                    $thumbnail_url = '';
                    if (has_post_thumbnail($post_id)) {
                        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    
                    // 日付を取得
                    $post_date = get_the_date('Y年m月d日', $post_id);
                    ?>
                    <li class="p-top-news-item">
                        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="p-top-news-link">
                            <?php if ($thumbnail_url) : ?>
                                <div class="p-top-news-thumbnail">
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" class="p-top-news-image">
                                </div>
                            <?php endif; ?>
                            <div class="p-top-news-content">
                                <h3 class="p-top-news-title-item">
                                    <?php echo esc_html(get_the_title($post_id)); ?>
                                </h3>
                                <div class="p-top-news-meta">
                                    <?php if (!empty($post_date)) : ?>
                                        <time class="p-top-news-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                            <?php echo esc_html($post_date); ?>
                                        </time>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </ul>
    </section>
<?php endif; ?>


