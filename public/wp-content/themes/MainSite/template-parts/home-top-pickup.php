<?php
/**
 * Template part for displaying top pickup section
 *
 * @package wp-multi-subdomain.idemii.tech
 */

// 投稿タイプ「top_pickup」の一覧を取得
$top_pickup_query = new WP_Query(array(
    'post_type'      => 'top_pickup',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
));

if ($top_pickup_query->have_posts()) :
    ?>
    <section class="p-top-pickup-list">
        <?php
        while ($top_pickup_query->have_posts()) :
            $top_pickup_query->the_post();
            $post_id = get_the_ID();

            // ACFフィールドを取得
            $image_pc   = function_exists('get_field') ? get_field('image_pc', $post_id) : '';
            $image_sp   = function_exists('get_field') ? get_field('image_sp', $post_id) : '';
            $catch_copy = function_exists('get_field') ? get_field('catch_copy', $post_id) : '';
            $lead_text  = function_exists('get_field') ? get_field('lead_text', $post_id) : '';
            
            // ボタン関連のACFフィールドを取得
            $btn_text  = function_exists('get_field') ? get_field('btn_text', $post_id) : '';
            $link_data = function_exists('get_field') ? get_field('link_data', $post_id) : '';
            
            // リンクURLとtarget属性を決定
            // 返り値は「リンク配列」に設定されています
            $button_url = '';
            $button_target = '';
            $button_rel = '';
            
            if (!empty($link_data)) {
                // 配列形式（返り値: 「リンク配列」）
                if (is_array($link_data)) {
                    // urlキーがある場合（標準的なACFリンクフィールドの配列形式）
                    if (isset($link_data['url'])) {
                        $button_url = $link_data['url'];
                        // target属性を確認（「リンクを新しいタブで開く」がチェックされている場合）
                        if (isset($link_data['target']) && $link_data['target'] === '_blank') {
                            $button_target = '_blank';
                            $button_rel = 'noopener noreferrer';
                        }
                    }
                    // 数値キーの配列の場合
                    elseif (isset($link_data[0]) && is_string($link_data[0])) {
                        $button_url = $link_data[0];
                    }
                }
                // 文字列形式（念のため対応）
                elseif (is_string($link_data) && !empty($link_data)) {
                    $button_url = $link_data;
                }
            }

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
            <article class="p-top-pickup-item">
                <?php if ($image_pc_url || $image_sp_url) : ?>
                    <picture class="p-top-pickup-image">
                        <?php if ($image_sp_url) : ?>
                            <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_sp_url); ?>">
                        <?php endif; ?>
                        <?php if ($image_pc_url) : ?>
                            <img src="<?php echo esc_url($image_pc_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                        <?php endif; ?>
                    </picture>
                <?php endif; ?>

                <div class="p-top-pickup-body">
                    <?php if (!empty($catch_copy)) : ?>
                        <h2 class="p-top-pickup-catch"><?php echo nl2br(esc_html($catch_copy)); ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($lead_text)) : ?>
                        <p class="p-top-pickup-lead"><?php echo nl2br(esc_html($lead_text)); ?></p>
                    <?php endif; ?>
                    
                    <?php 
                    // ボタンを表示（btn_textとbutton_urlの両方が存在する場合のみ表示）
                    if (!empty($btn_text) && !empty($button_url)) : ?>
                        <div class="p-top-pickup-button">
                            <a href="<?php echo esc_url($button_url); ?>" 
                               class="p-top-pickup-btn"
                               <?php if (!empty($button_target)) : ?>target="<?php echo esc_attr($button_target); ?>"<?php endif; ?>
                               <?php if (!empty($button_rel)) : ?>rel="<?php echo esc_attr($button_rel); ?>"<?php endif; ?>>
                                <?php echo esc_html($btn_text); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </section>
<?php else : ?>
    <p>表示するトップピックアップがありません。</p>
<?php endif; ?>

