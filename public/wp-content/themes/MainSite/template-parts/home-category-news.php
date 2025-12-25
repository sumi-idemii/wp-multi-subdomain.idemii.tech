<?php
/**
 * Template part for displaying category news section
 * top_category_areaのpage_categoryタクソノミーに基づいてニュース記事を表示
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

// ニュース「news」の記事を取得（page_categoryタクソノミーで絞り込み）
$category_news_query = new WP_Query(array(
    'post_type'      => 'news',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => array(
        array(
            'taxonomy' => 'page_category',
            'field'    => 'term_id',
            'terms'    => $page_category_ids,
            'operator' => 'IN'
        )
    ),
));

if ($category_news_query->have_posts()) :
    ?>
    <section class="p-top-category-news">
        <h2 class="p-top-category-news-title">ニュース</h2>
        <ul class="p-top-category-news-list">
            <?php
            while ($category_news_query->have_posts()) :
                $category_news_query->the_post();
                $post_id = get_the_ID();
                
                // キャッチ画像を取得
                $thumbnail_url = '';
                if (has_post_thumbnail($post_id)) {
                    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                }
                
                // 日付を取得
                $post_date = get_the_date('Y年m月d日', $post_id);
                ?>
                <li class="p-top-category-news-item">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="p-top-category-news-link">
                        <?php if ($thumbnail_url) : ?>
                            <div class="p-top-category-news-thumbnail">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" class="p-top-category-news-image">
                            </div>
                        <?php endif; ?>
                        <div class="p-top-category-news-content">
                            <h3 class="p-top-category-news-title-item">
                                <?php echo esc_html(get_the_title($post_id)); ?>
                            </h3>
                            <div class="p-top-category-news-meta">
                                <?php if (!empty($post_date)) : ?>
                                    <time class="p-top-category-news-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
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
            ?>
        </ul>
    </section>
<?php endif; ?>


