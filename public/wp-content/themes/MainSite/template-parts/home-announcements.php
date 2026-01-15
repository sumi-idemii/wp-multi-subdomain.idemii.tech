<?php
/**
 * Template part for displaying announcements section
 * トップページ表示にチェックがONのお知らせ記事を表示
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// チェックがONのお知らせ記事を取得（マルチサイト対応）
$announcements_posts = get_top_page_posts_by_type('announcements');

// お知らせ記事が存在する場合のみ表示
if (!empty($announcements_posts)) :
    // 最大3件に制限
    $announcements_posts = array_slice($announcements_posts, 0, 3);
    
    // 記事データを直接使用（WP_Queryを使わない）
    $display_announcements_posts = $announcements_posts;
else :
    // チェックがONの記事がない場合、従来通り最新3件を取得
    $notice_query = new WP_Query(array(
        'post_type'      => 'announcements',
        'posts_per_page' => 3,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    $display_announcements_posts = null; // WP_Queryを使用
endif;

// 表示するお知らせ記事がある場合
if (!empty($display_announcements_posts) || (isset($notice_query) && $notice_query->have_posts())) :
    ?>
    <section class="p-top-notice">
        <h2 class="p-top-notice-title">お知らせ</h2>
        <ul class="p-top-notice-list">
            <?php
            if (!empty($display_announcements_posts)) :
                // チェックがONの記事を表示（マルチサイト対応）
                foreach ($display_announcements_posts as $post_data) :
                    $post_id = $post_data['id'];
                    $post_title = $post_data['title'];
                    $post_permalink = '';
                    $post_date = '';
                    
                    // 該当サイトに切り替え
                    if (is_multisite() && isset($post_data['site_id'])) {
                        switch_to_blog($post_data['site_id']);
                    }
                    
                    // 日付を取得
                    $post_date = date('Y年m月d日', strtotime($post_data['date']));
                    $post_permalink = get_permalink($post_id);
                    
                    // マルチサイト環境の場合、元のサイトに戻す
                    if (is_multisite() && isset($post_data['site_id'])) {
                        restore_current_blog();
                    }
                    ?>
                    <li class="p-top-notice-item">
                        <a href="<?php echo esc_url($post_permalink); ?>" class="p-top-notice-link">
                            <div class="p-top-notice-meta">
                                <?php if (!empty($post_date)) : ?>
                                    <time class="p-top-notice-date" datetime="<?php echo esc_attr($post_data['date']); ?>">
                                        <?php echo esc_html($post_date); ?>
                                    </time>
                                <?php endif; ?>
                            </div>
                            <h3 class="p-top-notice-title-item">
                                <?php echo esc_html($post_title); ?>
                            </h3>
                        </a>
                    </li>
                    <?php
                endforeach;
            else :
                // 従来通りWP_Queryを使用
                while ($notice_query->have_posts()) :
                    $notice_query->the_post();
                    $post_id = get_the_ID();
                    
                    // 日付を取得
                    $post_date = get_the_date('Y年m月d日', $post_id);
                    ?>
                    <li class="p-top-notice-item">
                        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="p-top-notice-link">
                            <div class="p-top-notice-meta">
                                <?php if (!empty($post_date)) : ?>
                                    <time class="p-top-notice-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                        <?php echo esc_html($post_date); ?>
                                    </time>
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
            endif;
            ?>
        </ul>
    </section>
<?php endif; ?>


