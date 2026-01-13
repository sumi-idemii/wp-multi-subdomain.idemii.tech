<?php
/**
 * Template part for displaying related site section
 * トップページ関連サイト紹介エリア
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// 投稿タイプ「related_site」の一覧を取得
$related_site_query = new WP_Query(array(
    'post_type'      => 'related_site',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
));

if ($related_site_query->have_posts()) :
    ?>
    <section class="p-top-related-site">
        <ul class="p-top-related-site-list">
            <?php
            while ($related_site_query->have_posts()) :
                $related_site_query->the_post();
                $post_id = get_the_ID();

                // ACFフィールドを取得
                $site_name  = function_exists('get_field') ? get_field('site_name', $post_id) : '';
                $catch_copy = function_exists('get_field') ? get_field('catch_copy', $post_id) : '';
                $lead_text  = function_exists('get_field') ? get_field('lead_text', $post_id) : '';
                $image_icon = function_exists('get_field') ? get_field('image_icon', $post_id) : '';
                $url        = function_exists('get_field') ? get_field('url', $post_id) : '';

                // 画像URLを解決（ACFの返り値がID/配列/URLのどれでも対応）
                $image_icon_url = '';
                if (is_array($image_icon) && isset($image_icon['url'])) {
                    $image_icon_url = $image_icon['url'];
                } elseif (!empty($image_icon)) {
                    if (is_numeric($image_icon)) {
                        // 画像IDとして扱う
                        $image_icon_url = wp_get_attachment_image_url((int) $image_icon, 'full');
                    } else {
                        // 文字列の場合はURLとして扱う
                        $image_icon_url = $image_icon;
                    }
                }

                // URLが空の場合はスキップ
                if (empty($url)) {
                    continue;
                }
                ?>
                <li class="p-top-related-site-item">
                    <a href="<?php echo esc_url($url); ?>" class="p-top-related-site-link" target="_blank" rel="noopener noreferrer">
                        <?php if ($image_icon_url) : ?>
                            <div class="p-top-related-site-icon">
                                <img src="<?php echo esc_url($image_icon_url); ?>" alt="<?php echo esc_attr($site_name ? $site_name : get_the_title()); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-top-related-site-body">
                            <?php if (!empty($site_name)) : ?>
                                <h3 class="p-top-related-site-name"><?php echo esc_html($site_name); ?></h3>
                            <?php endif; ?>
                            
                            <?php if (!empty($catch_copy)) : ?>
                                <p class="p-top-related-site-catch"><?php echo nl2br(esc_html($catch_copy)); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($lead_text)) : ?>
                                <p class="p-top-related-site-lead"><?php echo nl2br(esc_html($lead_text)); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </ul>
    </section>
<?php else : ?>
    <!-- 表示する関連サイトがありません -->
<?php endif; ?>




