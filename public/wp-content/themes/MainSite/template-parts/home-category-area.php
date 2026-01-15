<?php
/**
 * Template part for displaying top category area section
 *
 * @package wp-multi-subdomain.idemii.tech
 * @param array $args {
 *     @type int $setting_no setting_noの値（デフォルト: 1）
 * }
 */

// setting_noを引数から取得（デフォルトは1）
$setting_no = isset($args['setting_no']) ? intval($args['setting_no']) : 1;

// トップページカテゴリ紹介エリア（setting_noで指定された記事を取得）
$top_category_query = new WP_Query(array(
    'post_type'      => 'top_category_area',
    'posts_per_page' => 1,
    'meta_query'     => array(
        array(
            'key'     => 'setting_no',
            'value'   => $setting_no,
            'compare' => '='
        )
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
));

if ($top_category_query->have_posts()) :
    while ($top_category_query->have_posts()) :
        $top_category_query->the_post();
        $post_id = get_the_ID();
        
        // ACFフィールドを取得
        $setting_image = function_exists('get_field') ? get_field('setting_image', $post_id) : '';
        $image_pc1 = function_exists('get_field') ? get_field('image_pc1', $post_id) : '';
        $image_pc2 = function_exists('get_field') ? get_field('image_pc2', $post_id) : '';
        $image_sp1 = function_exists('get_field') ? get_field('image_sp1', $post_id) : '';
        $image_sp2 = function_exists('get_field') ? get_field('image_sp2', $post_id) : '';
        $catch_copy = function_exists('get_field') ? get_field('catch_copy', $post_id) : '';
        $lead_text = function_exists('get_field') ? get_field('lead_text', $post_id) : '';
        
        // 画像位置を決定（左 or 右）
        $image_position = ($setting_image === '右') ? 'right' : 'left';
        
        // 画像URLを解決
        $image_pc1_url = '';
        if (is_array($image_pc1) && isset($image_pc1['url'])) {
            $image_pc1_url = $image_pc1['url'];
        } elseif (!empty($image_pc1)) {
            if (is_numeric($image_pc1)) {
                $image_pc1_url = wp_get_attachment_image_url((int) $image_pc1, 'full');
            } else {
                $image_pc1_url = $image_pc1;
            }
        }
        
        $image_pc2_url = '';
        if (is_array($image_pc2) && isset($image_pc2['url'])) {
            $image_pc2_url = $image_pc2['url'];
        } elseif (!empty($image_pc2)) {
            if (is_numeric($image_pc2)) {
                $image_pc2_url = wp_get_attachment_image_url((int) $image_pc2, 'full');
            } else {
                $image_pc2_url = $image_pc2;
            }
        }
        
        $image_sp1_url = '';
        if (is_array($image_sp1) && isset($image_sp1['url'])) {
            $image_sp1_url = $image_sp1['url'];
        } elseif (!empty($image_sp1)) {
            if (is_numeric($image_sp1)) {
                $image_sp1_url = wp_get_attachment_image_url((int) $image_sp1, 'full');
            } else {
                $image_sp1_url = $image_sp1;
            }
        }
        
        $image_sp2_url = '';
        if (is_array($image_sp2) && isset($image_sp2['url'])) {
            $image_sp2_url = $image_sp2['url'];
        } elseif (!empty($image_sp2)) {
            if (is_numeric($image_sp2)) {
                $image_sp2_url = wp_get_attachment_image_url((int) $image_sp2, 'full');
            } else {
                $image_sp2_url = $image_sp2;
            }
        }
        ?>
        <section class="p-top-category-area p-top-category-area-<?php echo esc_attr($image_position); ?>">
            <article class="p-top-category-item">
                <?php if ($image_position === 'left') : ?>
                    <!-- 画像が左側の場合 -->
                    <div class="p-top-category-images">
                        <?php if ($image_pc1_url || $image_sp1_url) : ?>
                            <picture class="p-top-category-image">
                                <?php if ($image_sp1_url) : ?>
                                    <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp1_url); ?>">
                                <?php endif; ?>
                                <?php if ($image_pc1_url) : ?>
                                    <img src="<?php echo esc_url($image_pc1_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php endif; ?>
                            </picture>
                        <?php endif; ?>
                        <?php if ($image_pc2_url || $image_sp2_url) : ?>
                            <picture class="p-top-category-image">
                                <?php if ($image_sp2_url) : ?>
                                    <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp2_url); ?>">
                                <?php endif; ?>
                                <?php if ($image_pc2_url) : ?>
                                    <img src="<?php echo esc_url($image_pc2_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php endif; ?>
                            </picture>
                        <?php endif; ?>
                    </div>
                    <div class="p-top-category-content">
                        <?php if (!empty($catch_copy)) : ?>
                            <h2 class="p-top-category-catch"><?php echo nl2br(esc_html($catch_copy)); ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($lead_text)) : ?>
                            <p class="p-top-category-lead"><?php echo nl2br(esc_html($lead_text)); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <!-- 画像が右側の場合 -->
                    <div class="p-top-category-content">
                        <?php if (!empty($catch_copy)) : ?>
                            <h2 class="p-top-category-catch"><?php echo nl2br(esc_html($catch_copy)); ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($lead_text)) : ?>
                            <p class="p-top-category-lead"><?php echo nl2br(esc_html($lead_text)); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="p-top-category-images">
                        <?php if ($image_pc1_url || $image_sp1_url) : ?>
                            <picture class="p-top-category-image">
                                <?php if ($image_sp1_url) : ?>
                                    <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp1_url); ?>">
                                <?php endif; ?>
                                <?php if ($image_pc1_url) : ?>
                                    <img src="<?php echo esc_url($image_pc1_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php endif; ?>
                            </picture>
                        <?php endif; ?>
                        <?php if ($image_pc2_url || $image_sp2_url) : ?>
                            <picture class="p-top-category-image">
                                <?php if ($image_sp2_url) : ?>
                                    <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp2_url); ?>">
                                <?php endif; ?>
                                <?php if ($image_pc2_url) : ?>
                                    <img src="<?php echo esc_url($image_pc2_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                <?php endif; ?>
                            </picture>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>
        
        <?php
        // page_categoryタクソノミーを取得
        $page_categories = get_the_terms($post_id, 'page_category');
        $page_category_ids = array();
        
        if ($page_categories && !is_wp_error($page_categories)) {
            foreach ($page_categories as $category) {
                $page_category_ids[] = $category->term_id;
            }
        }
        
        // page_categoryタクソノミーが設定されている場合、ニュースエリアを表示
        if (!empty($page_category_ids)) {
            // ニュースエリアのテンプレートパーツを読み込む
            get_template_part('template-parts/home', 'category-news', array(
                'page_category_ids' => $page_category_ids
            ));
        }
        ?>
        
        <?php
    endwhile;
    wp_reset_postdata();
endif;
?>



