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
                    echo '<p><strong>運営チーム:</strong> ' . esc_html(implode(', ', $team_names)) . '</p>';
                }
            }
            
            // 開催日時を表示（1〜12まで）
            if (function_exists('parse_datetime_from_field')) {
                for ($i = 1; $i <= 12; $i++) {
                    $suffix = '_' . $i;
                    $start_date = get_field('events_start_date' . $suffix, $post_id);
                    $end_date = get_field('events_end_date' . $suffix, $post_id);
                    $date_text = get_field('events_date_text' . $suffix, $post_id);
                    
                    // 開始日時が存在する場合のみ表示
                    if (!empty($start_date)) {
                        // イベント開催テキストを表示（存在する場合のみ）
                        if (!empty($date_text)) {
                            echo '<p><strong>イベント開催テキスト' . $i . ':</strong> ' . esc_html($date_text) . '</p>';
                        }
                        
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
    
    // カレンダー追加ボタンを表示
    if (function_exists('get_google_calendar_url') && function_exists('get_ical_file_url')) {
        $google_url = get_google_calendar_url($post_id);
        $ical_url = get_ical_file_url($post_id);
        ?>
        <div class="addition">
            <p>カレンダーに追加</p>
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

