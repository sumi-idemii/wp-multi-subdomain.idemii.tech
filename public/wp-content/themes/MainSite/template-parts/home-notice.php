<?php
/**
 * Template part for displaying notice section
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// お知らせ「notice」の最新3件を取得
$notice_query = new WP_Query(array(
    'post_type'      => 'notice',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

if ($notice_query->have_posts()) :
    ?>
    <section class="p-top-notice">
        <h2 class="p-top-notice-title">お知らせ</h2>
        <ul class="p-top-notice-list">
            <?php
            while ($notice_query->have_posts()) :
                $notice_query->the_post();
                $post_id = get_the_ID();
                
                // 日付を取得
                $post_date = get_the_date('Y年m月d日', $post_id);
                
                // notice_categoryタクソノミーを取得
                $notice_categories = get_the_terms($post_id, 'notice_category');
                $category_names = array();
                if ($notice_categories && !is_wp_error($notice_categories)) {
                    foreach ($notice_categories as $category) {
                        $category_names[] = $category->name;
                    }
                }
                ?>
                <li class="p-top-notice-item">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="p-top-notice-link">
                        <div class="p-top-notice-meta">
                            <?php if (!empty($post_date)) : ?>
                                <time class="p-top-notice-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                    <?php echo esc_html($post_date); ?>
                                </time>
                            <?php endif; ?>
                            <?php if (!empty($category_names)) : ?>
                                <span class="p-top-notice-category">
                                    <?php echo esc_html(implode(', ', $category_names)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="p-top-notice-title-item">
                            <?php echo esc_html(get_the_title($post_id)); ?>
                        </h3>
                    </a>
                </li>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </ul>
    </section>
<?php endif; ?>

