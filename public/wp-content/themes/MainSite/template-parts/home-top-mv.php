<?php
/**
 * Template part for displaying top main visual section
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// 投稿タイプ「top_mv」の一覧を取得
$top_mv_query = new WP_Query(array(
    'post_type'      => 'top_mv',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
));

if ($top_mv_query->have_posts()) :
    ?>
    <section class="p-top-mv-list">
        <?php
        while ($top_mv_query->have_posts()) :
            $top_mv_query->the_post();
            $post_id = get_the_ID();

            // ACFフィールドを取得
            $image_pc   = function_exists('get_field') ? get_field('image_pc', $post_id) : '';
            $image_sp   = function_exists('get_field') ? get_field('image_sp', $post_id) : '';
            $catch_copy = function_exists('get_field') ? get_field('catch_copy', $post_id) : '';
            $lead_text  = function_exists('get_field') ? get_field('lead_text', $post_id) : '';

            // 画像URLを解決（ACFの返り値がID/配列/URLのどれでも対応）
            $image_pc_url = '';
            if (is_array($image_pc) && isset($image_pc['url'])) {
                $image_pc_url = $image_pc['url'];
            } elseif (!empty($image_pc)) {
                if (is_numeric($image_pc)) {
                    // 画像IDとして扱う
                    $image_pc_url = wp_get_attachment_image_url((int) $image_pc, 'full');
                } else {
                    // 文字列の場合はURLとして扱う
                    $image_pc_url = $image_pc;
                }
            }

            $image_sp_url = '';
            if (is_array($image_sp) && isset($image_sp['url'])) {
                $image_sp_url = $image_sp['url'];
            } elseif (!empty($image_sp)) {
                if (is_numeric($image_sp)) {
                    // 画像IDとして扱う
                    $image_sp_url = wp_get_attachment_image_url((int) $image_sp, 'full');
                } else {
                    // 文字列の場合はURLとして扱う
                    $image_sp_url = $image_sp;
                }
            }
            ?>
            <article class="p-top-mv-item">
                <?php if ($image_pc_url || $image_sp_url) : ?>
                    <picture class="p-top-mv-image">
                        <?php if ($image_sp_url) : ?>
                            <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp_url); ?>">
                        <?php endif; ?>
                        <?php if ($image_pc_url) : ?>
                            <img src="<?php echo esc_url($image_pc_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                        <?php endif; ?>
                    </picture>
                <?php endif; ?>

                <div class="p-top-mv-body">
                    <?php if (!empty($catch_copy)) : ?>
                        <h2 class="p-top-mv-catch"><?php echo nl2br(esc_html($catch_copy)); ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($lead_text)) : ?>
                        <p class="p-top-mv-lead"><?php echo nl2br(esc_html($lead_text)); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </section>
<?php else : ?>
    <p>表示するトップMVがありません。</p>
<?php endif; ?>

