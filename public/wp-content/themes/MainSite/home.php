<?php
/**
 * The template for displaying the home (top) page
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();
?>

<p class="temp-name">テンプレート：/home.php</p>

<main class="p-top-mv" id="content">
    <?php
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

    <?php
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

    <?php
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

    <?php
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

    <?php
    // イベント記事の最新8件を取得
    $events_query = new WP_Query(array(
        'post_type'      => 'events',
        'posts_per_page' => 8,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if ($events_query->have_posts()) :
        while ($events_query->have_posts()) :
            $events_query->the_post();
            $post_id = get_the_ID();
            ?>
            <section class="p-top-events">
                <article class="p-top-events-item">
                    <?php
                    // タイトルを表示
                    the_title('<h2 class="p-top-events-title">', '</h2>');
                    
                    // キャッチ画像を表示
                    if (has_post_thumbnail($post_id)) {
                        echo '<div class="p-top-events-featured-image">';
                        the_post_thumbnail('large', array('class' => 'p-top-events-thumbnail'));
                        echo '</div>';
                    }
                    
                    // ACFフィールドを取得して表示
                    if (function_exists('get_field')) {
                        ?>
                        <div class="p-top-events-fields">
                            <?php
                            // 会場 (events_venue) - タクソノミー
                            $venue_field = get_field('events_venue', $post_id);
                            if ($venue_field) {
                                $venue_names = array();
                                $venue_ids = is_array($venue_field) ? $venue_field : array($venue_field);
                                
                                $venue_taxonomy = 'events_venue';
                                if (!taxonomy_exists($venue_taxonomy)) {
                                    $venue_taxonomy = 'events-venue';
                                }
                                
                                foreach ($venue_ids as $venue_id) {
                                    if (is_numeric($venue_id)) {
                                        $term = get_term($venue_id, $venue_taxonomy);
                                        if ($term && !is_wp_error($term)) {
                                            $venue_names[] = $term->name;
                                        }
                                    } elseif (is_object($venue_id) && get_class($venue_id) === 'WP_Term') {
                                        $venue_names[] = $venue_id->name;
                                    }
                                }
                                
                                if (!empty($venue_names)) {
                                    echo '<p><strong>会場:</strong> ' . esc_html(implode(', ', $venue_names)) . '</p>';
                                }
                            }
                            
                            // 対象参加者 (events_target_visitors) - タクソノミー
                            $target_visitors_field = get_field('events_target_visitors', $post_id);
                            if ($target_visitors_field) {
                                $visitor_names = array();
                                $visitor_ids = is_array($target_visitors_field) ? $target_visitors_field : array($target_visitors_field);
                                
                                $visitor_taxonomy = 'events_target_visitors';
                                if (!taxonomy_exists($visitor_taxonomy)) {
                                    $visitor_taxonomy = 'events-target-visitors';
                                }
                                
                                foreach ($visitor_ids as $visitor_id) {
                                    if (is_numeric($visitor_id)) {
                                        $term = get_term($visitor_id, $visitor_taxonomy);
                                        if ($term && !is_wp_error($term)) {
                                            $visitor_names[] = $term->name;
                                        }
                                    } elseif (is_object($visitor_id) && get_class($visitor_id) === 'WP_Term') {
                                        $visitor_names[] = $visitor_id->name;
                                    }
                                }
                                
                                if (!empty($visitor_names)) {
                                    echo '<p><strong>対象参加者:</strong> ' . esc_html(implode(', ', $visitor_names)) . '</p>';
                                }
                            }
                            
                            // 使用言語 (events_language) - チェックボックス
                            $language_field = get_field('events_language', $post_id);
                            if ($language_field) {
                                $languages = is_array($language_field) ? $language_field : array($language_field);
                                if (!empty($languages)) {
                                    echo '<p><strong>使用言語:</strong> ' . esc_html(implode(', ', $languages)) . '</p>';
                                }
                            }
                            
                            // 費用 (events_participation_fee) - テキスト
                            $fee_field = get_field('events_participation_fee', $post_id);
                            if ($fee_field) {
                                echo '<p><strong>費用:</strong> ' . esc_html($fee_field) . '</p>';
                            }
                            
                            // 運営チーム (events_team) - タクソノミー（color属性付き）
                            $team_field = get_field('events_team', $post_id);
                            if ($team_field) {
                                $team_items = array();
                                $team_ids = is_array($team_field) ? $team_field : array($team_field);
                                
                                $team_taxonomy = 'events_team';
                                if (!taxonomy_exists($team_taxonomy)) {
                                    $team_taxonomy = 'events-team';
                                }
                                
                                foreach ($team_ids as $team_id) {
                                    $term = null;
                                    
                                    if (is_numeric($team_id)) {
                                        $term = get_term($team_id, $team_taxonomy);
                                    } elseif (is_object($team_id) && get_class($team_id) === 'WP_Term') {
                                        $term = $team_id;
                                    }
                                    
                                    if ($term && !is_wp_error($term)) {
                                        // color属性を取得
                                        $color = '';
                                        if (function_exists('get_taxonomy_term_color')) {
                                            $color = get_taxonomy_term_color($term);
                                        } else {
                                            // get_taxonomy_term_color関数が利用できない場合のフォールバック
                                            $color = get_field('color', $term);
                                            if ($color === false || $color === null || $color === '') {
                                                $color = get_field('color', 'taxonomy_events_team_' . $term->term_id);
                                            }
                                        }
                                        
                                        // ターム名とcolorを組み合わせて表示
                                        $team_display = esc_html($term->name);
                                        if (!empty($color)) {
                                            $team_display .= ' (color: ' . esc_html($color) . ')';
                                        }
                                        $team_items[] = $team_display;
                                    }
                                }
                                
                                if (!empty($team_items)) {
                                    echo '<p><strong>運営チーム:</strong> ' . implode(', ', $team_items) . '</p>';
                                }
                            }
                            
                            // イベント開催テキストを取得
                            $date_text = get_field('events_date_text', $post_id);
                            if (empty($date_text)) {
                                $date_text = get_field('events-date-text', $post_id);
                            }
                            
                            // イベント開催テキストを表示（存在する場合のみ）
                            if (!empty($date_text)) {
                                echo '<p><strong>イベント開催テキスト入力:</strong> ' . esc_html($date_text) . '</p>';
                            }
                            
                            // 開催日時を表示（events_date_textが空の場合のみ）
                            if (empty($date_text) && function_exists('parse_datetime_from_field')) {
                                for ($i = 1; $i <= 12; $i++) {
                                    $suffix = '_' . $i;
                                    $start_date = get_field('events_start_date' . $suffix, $post_id);
                                    $end_date = get_field('events_end_date' . $suffix, $post_id);
                                    
                                    // 開始日時が存在する場合のみ表示
                                    if (!empty($start_date)) {
                                        $start_datetime = parse_datetime_from_field($start_date);
                                        $end_datetime = !empty($end_date) ? parse_datetime_from_field($end_date) : false;
                                        
                                        if ($start_datetime) {
                                            // 開始日時の表示形式
                                            $start_year = (int)$start_datetime->format('Y');
                                            $start_month = (int)$start_datetime->format('m');
                                            $start_day = (int)$start_datetime->format('d');
                                            $start_time = $start_datetime->format('H:i');
                                            
                                            $start_display = $start_year . '年' . $start_month . '月' . $start_day . '日 ' . $start_time;
                                            
                                            if ($end_datetime) {
                                                // 終了日時の表示形式（条件に応じて変更）
                                                $end_year = (int)$end_datetime->format('Y');
                                                $end_month = (int)$end_datetime->format('m');
                                                $end_day = (int)$end_datetime->format('d');
                                                $end_time = $end_datetime->format('H:i');
                                                
                                                // 条件1: 同じ日の場合（年月日がすべて同じ）
                                                if ($start_year === $end_year && $start_month === $end_month && $start_day === $end_day) {
                                                    $end_display = $end_time;
                                                }
                                                // 条件2: 同じ月で日が異なる場合
                                                elseif ($start_year === $end_year && $start_month === $end_month) {
                                                    $end_display = $end_day . '日 ' . $end_time;
                                                }
                                                // 条件3: 月が異なる場合
                                                else {
                                                    $end_display = $end_month . '月' . $end_day . '日 ' . $end_time;
                                                }
                                                
                                                echo '<p><strong>開催日時' . $i . ':</strong> ' . esc_html($start_display) . ' 〜 ' . esc_html($end_display) . '</p>';
                                            } else {
                                                // 終了日時がない場合
                                                echo '<p><strong>開催日時' . $i . ':</strong> ' . esc_html($start_display) . '</p>';
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </article>
            </section>
            <?php
        endwhile;
        wp_reset_postdata();
    endif;
    ?>

    <?php
    // トップページカテゴリ紹介エリア（setting_noが「1」の記事を取得）
    $top_category_query = new WP_Query(array(
        'post_type'      => 'top_category_area',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => 'setting_no',
                'value'   => '1',
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
        endwhile;
        wp_reset_postdata();
    endif;
    ?>
</main>

<?php
// クッキーバナー
if (file_exists(get_template_directory() . '/template-parts/cookie-banner.php')) {
    include get_template_directory() . '/template-parts/cookie-banner.php';
}

get_footer();
