<?php
/**
 * The template for displaying single event posts
 *
 * @package wp-multi-subdomain.idemii.tech
 */

get_header();
?>

<p class="temp-name">テンプレート：/single-events.php</p>

<?php
while (have_posts()) :
    the_post();
    $post_id = get_the_ID();
    
    // 記事タイトルを表示
    the_title('<h1>', '</h1>');
    
    // キャッチ画像を表示
    if (has_post_thumbnail($post_id)) {
        echo '<div class="event-featured-image">';
        the_post_thumbnail('large', array('class' => 'event-thumbnail'));
        echo '</div>';
    }
    
    // 本文を表示
    the_content();
    
    // ACFフィールドを取得して表示
    if (function_exists('get_field')) {
        ?>
        <div class="event-fields">
            <?php
            // 会場 (events_venue) - テキストエリア
            $venue_field = get_field('events_venue', $post_id);
            if (!empty($venue_field)) {
                echo '<p><strong>' . esc_html(get_event_translation('venue')) . ':</strong> ' . nl2br(esc_html($venue_field)) . '</p>';
            }
            
            // 対象参加者 (events_target_visitors) - テキストエリア
            $target_visitors_field = get_field('events_target_visitors', $post_id);
            if (!empty($target_visitors_field)) {
                echo '<p><strong>' . esc_html(get_event_translation('target_visitors')) . ':</strong> ' . nl2br(esc_html($target_visitors_field)) . '</p>';
            }
            
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
            
            // 組織 (organisation) - タクソノミー（返り値：タームオブジェクト）
            $organisation_field = get_field('organisation', $post_id);
            $organisation_names = array();
            
            // ACFフィールドから取得を試行
            if ($organisation_field) {
                // 配列の場合（複数のタームオブジェクト）
                if (is_array($organisation_field) && !empty($organisation_field)) {
                    foreach ($organisation_field as $term) {
                        // WP_Termオブジェクトの場合
                        if (is_object($term)) {
                            // instanceof WP_Term または get_class() で確認
                            if ($term instanceof WP_Term) {
                                $organisation_names[] = $term->name;
                            } elseif (get_class($term) === 'WP_Term') {
                                $organisation_names[] = $term->name;
                            } elseif (isset($term->name)) {
                                // オブジェクトにnameプロパティがある場合
                                $organisation_names[] = $term->name;
                            }
                        }
                    }
                } elseif (is_object($organisation_field)) {
                    // 単一のWP_Termオブジェクトの場合
                    if ($organisation_field instanceof WP_Term) {
                        $organisation_names[] = $organisation_field->name;
                    } elseif (get_class($organisation_field) === 'WP_Term') {
                        $organisation_names[] = $organisation_field->name;
                    } elseif (isset($organisation_field->name)) {
                        // オブジェクトにnameプロパティがある場合
                        $organisation_names[] = $organisation_field->name;
                    }
                }
            }
            
            // ACFフィールドから取得できなかった場合、直接タクソノミーから取得
            if (empty($organisation_names)) {
                $organisation_terms = get_the_terms($post_id, 'organisation');
                if ($organisation_terms && !is_wp_error($organisation_terms)) {
                    foreach ($organisation_terms as $term) {
                        $organisation_names[] = $term->name;
                    }
                }
            }
            
            // 表示
            if (!empty($organisation_names)) {
                echo '<p><strong>' . esc_html(get_event_translation('team')) . ':</strong> ' . esc_html(implode(', ', $organisation_names)) . '</p>';
            }
            
            // イベント開催テキストを取得（1つのフィールドのみ）
            $date_text = get_field('events_date_text', $post_id);
            if (empty($date_text)) {
                $date_text = get_field('events-date-text', $post_id);
            }
            // イベント開催テキストを表示（存在する場合のみ）
            if (!empty($date_text)) {
                echo '<p><strong>' . esc_html(get_event_translation('date_text')) . ':</strong> ' . esc_html($date_text) . '</p>';
            }
            
            // 開催日時を表示（1〜12まで）
            if (function_exists('parse_datetime_from_field')) {
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
    
    // カレンダー追加ボタンを表示
    if (function_exists('get_google_calendar_url') && function_exists('get_ical_file_url')) {
        $google_url = get_google_calendar_url($post_id);
        $ical_url = get_ical_file_url($post_id);
        ?>
        <div class="addition">
            <p><?php echo esc_html(get_event_translation('add_to_calendar')); ?></p>
            <a href="<?php echo esc_url($google_url); ?>" target="_blank" rel="noopener noreferrer">
                <span class="i-ico ico-google">google</span>
            </a>
            <a href="<?php echo esc_url($ical_url); ?>" target="_blank" data-tooltip="iCal" aria-label="Save to iCal" title="Save to iCal" data-ga-event="" data-ga-category="Event" data-ga-action="Add to Calendar" data-ga-label="<?php echo esc_attr(get_the_title()); ?> - iCal" rel="noopener noreferrer">
                <span class="i-ico ico-apple">apple</span>
            </a>
            <a href="<?php echo esc_url($ical_url); ?>" target="_blank" data-tooltip="Outlook" aria-label="Save to Outlook" title="Save to Outlook" data-ga-event="" data-ga-category="Event" data-ga-action="Add to Calendar" data-ga-label="<?php echo esc_attr(get_the_title()); ?> - Outlook" rel="noopener noreferrer">
                <span class="i-ico ico-other">Outlook</span>
            </a>
        </div>
        <?php
    }
    
endwhile;
?>

<?php
get_footer();

