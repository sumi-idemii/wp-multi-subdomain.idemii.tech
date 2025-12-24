<?php
/**
 * Template part for displaying news section
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// ニュース「news」の最新8件を取得
$news_query = new WP_Query(array(
    'post_type'      => 'news',
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

if ($news_query->have_posts()) :
    ?>
    <section class="p-top-news">
        <h2 class="p-top-news-title">ニュース</h2>
        <ul class="p-top-news-list">
            <?php
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
                
                // news_categoryタクソノミーを取得
                $news_categories = get_the_terms($post_id, 'news_category');
                $category_names = array();
                if ($news_categories && !is_wp_error($news_categories)) {
                    foreach ($news_categories as $category) {
                        $category_names[] = $category->name;
                    }
                }
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
                                <?php if (!empty($category_names)) : ?>
                                    <span class="p-top-news-category">
                                        <?php echo esc_html(implode(', ', $category_names)); ?>
                                    </span>
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


