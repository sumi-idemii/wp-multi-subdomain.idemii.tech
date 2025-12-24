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
            // 会場 (events_venue) - タクソノミー
            $venue_field = get_field('events_venue', $post_id);
            if ($venue_field) {
                $venue_names = array();
                $venue_ids = is_array($venue_field) ? $venue_field : array($venue_field);
                
                // タクソノミー名を取得（ハイフン形式とアンダースコア形式の両方を試す）
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
                    echo '<p><strong>' . esc_html(get_event_translation('venue')) . ':</strong> ' . esc_html(implode(', ', $venue_names)) . '</p>';
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
                    echo '<p><strong>' . esc_html(get_event_translation('target_visitors')) . ':</strong> ' . esc_html(implode(', ', $visitor_names)) . '</p>';
                }
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
            
            // 運営チーム (events_team) - タクソノミー
            $team_field = get_field('events_team', $post_id);
            if ($team_field) {
                $team_names = array();
                $team_ids = is_array($team_field) ? $team_field : array($team_field);
                
                $team_taxonomy = 'events_team';
                if (!taxonomy_exists($team_taxonomy)) {
                    $team_taxonomy = 'events-team';
                }
                
                foreach ($team_ids as $team_id) {
                    if (is_numeric($team_id)) {
                        $term = get_term($team_id, $team_taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $team_names[] = $term->name;
                        }
                    } elseif (is_object($team_id) && get_class($team_id) === 'WP_Term') {
                        $team_names[] = $team_id->name;
                    }
                }
                
                if (!empty($team_names)) {
                    echo '<p><strong>' . esc_html(get_event_translation('team')) . ':</strong> ' . esc_html(implode(', ', $team_names)) . '</p>';
                }
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

