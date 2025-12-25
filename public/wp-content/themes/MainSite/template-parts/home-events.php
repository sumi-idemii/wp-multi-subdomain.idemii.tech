<?php
/**
 * Template part for displaying events section
 *
 * @package wp-multi-subdomain.idemii.tech
 */

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
                        // 使用言語 (events_language) - チェックボックス
                        $language_field = get_field('events_language', $post_id);
                        if ($language_field) {
                            $languages = is_array($language_field) ? $language_field : array($language_field);
                            if (!empty($languages)) {
                                echo '<p><strong>' . esc_html(get_event_translation('language')) . ':</strong> ' . esc_html(implode(', ', $languages)) . '</p>';
                            }
                        }
                        
                        // 費用 (events_participation_fee) - テキスト
                        $fee_field = get_field('events_participation_fee', $post_id);
                        if ($fee_field) {
                            echo '<p><strong>' . esc_html(get_event_translation('fee')) . ':</strong> ' . esc_html($fee_field) . '</p>';
                        }
                        
                        // 組織 (organisation) - タクソノミー（color属性付き）
                        $organisation_field = get_field('organisation', $post_id);
                        
                        // フィールドが存在し、値がある場合のみ処理
                        if ($organisation_field !== false && $organisation_field !== null && $organisation_field !== '') {
                            $organisation_items = array();
                            $organisation_ids = is_array($organisation_field) ? $organisation_field : array($organisation_field);
                            
                            $organisation_taxonomy = 'organisation';
                            
                            foreach ($organisation_ids as $organisation_id) {
                                $organisation = null;
                                
                                if (is_numeric($organisation_id)) {
                                    $organisation = get_term($organisation_id, $organisation_taxonomy);
                                } elseif (is_object($organisation_id) && get_class($organisation_id) === 'WP_Term') {
                                    $organisation = $organisation_id;
                                }
                                
                                if ($organisation && !is_wp_error($organisation)) {
                                    // color属性を取得
                                    $color = '';
                                    if (function_exists('get_taxonomy_term_color')) {
                                        $color = get_taxonomy_term_color($organisation);
                                    } else {
                                        // get_taxonomy_term_color関数が利用できない場合のフォールバック
                                        $color = get_field('color', $organisation);
                                        if ($color === false || $color === null || $color === '') {
                                            $color = get_field('color', 'taxonomy_organisation_' . $organisation->term_id);
                                        }
                                    }
                                    
                                    // ターム名とcolorを組み合わせて表示
                                    $organisation_display = esc_html($organisation->name);
                                    if (!empty($color)) {
                                        $organisation_display .= ' (color: ' . esc_html($color) . ')';
                                    }
                                    $organisation_items[] = $organisation_display;
                                }
                            }
                            
                            if (!empty($organisation_items)) {
                                echo '<p><strong>' . esc_html(get_event_translation('team')) . ':</strong> ' . implode(', ', $organisation_items) . '</p>';
                            }
                        }
                        
                        // イベント開催テキストを取得
                        $date_text = get_field('events_date_text', $post_id);
                        if (empty($date_text)) {
                            $date_text = get_field('events-date-text', $post_id);
                        }
                        
                        // イベント開催テキストを表示（存在する場合のみ）
                        if (!empty($date_text)) {
                            echo '<p><strong>' . esc_html(get_event_translation('date_text')) . ':</strong> ' . esc_html($date_text) . '</p>';
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
                                        if ($end_datetime) {
                                            // 日付範囲の表示
                                            $date_range = format_event_date_range_by_language($start_datetime, $end_datetime);
                                            echo '<p><strong>' . esc_html(get_event_translation('event_date')) . $i . ':</strong> ' . esc_html($date_range['start']) . ' 〜 ' . esc_html($date_range['end']) . '</p>';
                                        } else {
                                            // 終了日時がない場合
                                            $start_display = format_event_date_by_language($start_datetime, 'start');
                                            echo '<p><strong>' . esc_html(get_event_translation('event_date')) . $i . ':</strong> ' . esc_html($start_display) . '</p>';
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

