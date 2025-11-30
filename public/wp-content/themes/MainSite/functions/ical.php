<?php
/**
 * iCalファイル生成機能
 * イベントをカレンダーに追加するための機能
 */

/**
 * iCalファイルを生成してダウンロード
 */
function generate_ical_file() {
    // 投稿IDを取得
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    if (!$post_id) {
        wp_die('Invalid post ID');
    }
    
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'events') {
        wp_die('Invalid event post');
    }
    
    // タイトル
    $title = get_the_title($post_id);
    
    // 説明（本文の最初の200文字）
    $description = wp_strip_all_tags(get_the_excerpt($post_id));
    if (empty($description)) {
        $content = get_post_field('post_content', $post_id);
        $description = wp_trim_words(wp_strip_all_tags($content), 30);
    }
    
    // URL
    $url = get_permalink($post_id);
    
    // 複数の日程セットを取得（1〜12まで対応）
    $event_schedules = get_event_schedules($post_id);
    
    // スケジュールが空の場合は空のVCALENDARを返す（エラーではなく）
    if (empty($event_schedules)) {
        $ical_content = "BEGIN:VCALENDAR\r\n";
        $ical_content .= "VERSION:2.0\r\n";
        $ical_content .= "PRODID:-//WordPress//Event Calendar//EN\r\n";
        $ical_content .= "CALSCALE:GREGORIAN\r\n";
        $ical_content .= "METHOD:PUBLISH\r\n";
        $ical_content .= "END:VCALENDAR\r\n";
        
        $filename = 'event-' . $post_id . '.ics';
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($ical_content));
        echo $ical_content;
        exit;
    }
    
    // iCalファイルの内容を生成
    $ical_content = "BEGIN:VCALENDAR\r\n";
    $ical_content .= "VERSION:2.0\r\n";
    $ical_content .= "PRODID:-//WordPress//Event Calendar//EN\r\n";
    $ical_content .= "CALSCALE:GREGORIAN\r\n";
    $ical_content .= "METHOD:PUBLISH\r\n";
    
    // 各日程セットに対してVEVENTを作成
    $timezone = new DateTimeZone('Asia/Tokyo');
    $base_uid = 'event-' . $post_id . '@' . parse_url(home_url(), PHP_URL_HOST);
    
    foreach ($event_schedules as $index => $schedule) {
        if (empty($schedule['start_date'])) {
            continue; // 開始日がなければスキップ
        }
        
        // 日時を解析（events_start_dateとevents_end_dateから直接取得）
        $start_datetime = parse_datetime_from_field($schedule['start_date']);
        $end_datetime = parse_datetime_from_field($schedule['end_date']);
        
        if (!$start_datetime) {
            // パース失敗の場合はスキップ（エラーログは本番環境では出力しない）
            continue;
        }
        
        // 終了日時がない場合は開始日時+1時間
        if (!$end_datetime) {
            $end_datetime = clone $start_datetime;
            $end_datetime->modify('+1 hour');
        }
        
        // UTC形式に変換（Appleカレンダー対応のため）
        // 日本時間（JST）からUTCに変換（9時間差）
        // parse_datetime_from_fieldはAsia/Tokyoタイムゾーンで作成されている
        // 確実にUTC変換するため、タイムゾーンを明示的に指定
        $jst_timezone = new DateTimeZone('Asia/Tokyo');
        $utc_timezone = new DateTimeZone('UTC');
        
        // 日本時間として明示的に作成してからUTCに変換
        $start_jst = new DateTime($start_datetime->format('Y-m-d H:i:s'), $jst_timezone);
        $start_utc = $start_jst->setTimezone($utc_timezone);
        
        $end_jst = new DateTime($end_datetime->format('Y-m-d H:i:s'), $jst_timezone);
        $end_utc = $end_jst->setTimezone($utc_timezone);
        
        // iCal形式の日時（UTC形式: YYYYMMDDTHHMMSSZ）
        $start_ical = $start_utc->format('Ymd\THis\Z');
        $end_ical = $end_utc->format('Ymd\THis\Z');
        
        // UID（一意のID、複数の日程がある場合は番号を追加）
        $uid = $base_uid . '-' . ($index + 1);
        
        // VEVENTを作成
        $ical_content .= "BEGIN:VEVENT\r\n";
        $ical_content .= "UID:" . $uid . "\r\n";
        $ical_content .= "DTSTART:" . $start_ical . "\r\n";
        $ical_content .= "DTEND:" . $end_ical . "\r\n";
        $ical_content .= "DTSTAMP:" . date('Ymd\THis\Z') . "\r\n";
        $ical_content .= "SEQUENCE:0\r\n";
        $ical_content .= format_ical_line("SUMMARY", escape_ical_text($title));
        if (!empty($description)) {
            $ical_content .= format_ical_line("DESCRIPTION", escape_ical_text($description));
        }
        if (!empty($schedule['location'])) {
            $ical_content .= format_ical_line("LOCATION", escape_ical_text($schedule['location']));
        }
        if (!empty($url)) {
            $ical_content .= "URL:" . $url . "\r\n";
        }
        $ical_content .= "END:VEVENT\r\n";
    }
    
    $ical_content .= "END:VCALENDAR\r\n";
    
    // ファイル名
    $filename = 'event-' . $post_id . '.ics';
    
    // ヘッダーを設定
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($ical_content));
    
    // 出力
    echo $ical_content;
    exit;
}

/**
 * ACFの日時フィールドからDateTimeオブジェクトを生成
 * 
 * @param string $datetime_string 日時文字列（ACFの日時フィールド形式）
 * @return DateTime|false DateTimeオブジェクト、失敗時はfalse
 */
function parse_datetime_from_field($datetime_string) {
    if (empty($datetime_string)) {
        return false;
    }
    
    $timezone = new DateTimeZone('Asia/Tokyo');
    
    // ACFの日時フィールドは複数の形式に対応
    // 'Y/m/d H:i:s' (例：'2025/11/06 14:00:00')
    // 'Y-m-d H:i:s' (例：'2025-11-28 14:00:00')
    // 'Y-m-d-H:i:s' (例：'2025-11-13-14:00:00') - ACFの特殊形式
    // 'Ymd' (例：'20251128')
    $datetime = false;
    
    // 形式1: 'Y-m-d-H:i:s' 形式（ハイフン3つ、ACFの特殊形式）
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})-(\d{2}):(\d{2})(:(\d{2}))?$/', $datetime_string, $matches)) {
        // ハイフンをスペースに置換してからパース
        $normalized = $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5];
        if (isset($matches[7])) {
            $normalized .= ':' . $matches[7];
        } else {
            $normalized .= ':00';
        }
        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $normalized, $timezone);
        if (!$datetime) {
            $datetime = DateTime::createFromFormat('Y-m-d H:i', $normalized, $timezone);
        }
    }
    // 形式2: 'Y/m/d H:i:s' または 'Y/m/d H:i' 形式（スラッシュ区切り）
    elseif (preg_match('/^\d{4}\/\d{2}\/\d{2}[\sT]\d{2}:\d{2}(:\d{2})?/', $datetime_string)) {
        $datetime = DateTime::createFromFormat('Y/m/d H:i:s', $datetime_string, $timezone);
        if (!$datetime) {
            $datetime = DateTime::createFromFormat('Y/m/d H:i', $datetime_string, $timezone);
        }
        if (!$datetime) {
            // 秒がない場合のフォールバック
            $datetime = DateTime::createFromFormat('Y/m/d H:i:s', $datetime_string . ':00', $timezone);
        }
    }
    // 形式3: 'Y-m-d H:i:s' または 'Y-m-d H:i' 形式（ハイフン区切り、通常形式）
    elseif (preg_match('/^\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}(:\d{2})?/', $datetime_string)) {
        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime_string, $timezone);
        if (!$datetime) {
            $datetime = DateTime::createFromFormat('Y-m-d H:i', $datetime_string, $timezone);
        }
    }
    // 形式4: 'YmdHis' 形式（例：'20251128140000'）
    elseif (preg_match('/^\d{14}$/', $datetime_string)) {
        $datetime = DateTime::createFromFormat('YmdHis', $datetime_string, $timezone);
    }
    // 形式5: 'Ymd' 形式（例：'20251128'）- 時刻がない場合は00:00:00
    elseif (preg_match('/^\d{8}$/', $datetime_string)) {
        $datetime = DateTime::createFromFormat('Ymd', $datetime_string, $timezone);
        if ($datetime) {
            $datetime->setTime(0, 0, 0);
        }
    }
    // 形式6: その他の形式を試す（最後の手段）
    else {
        // 様々な形式を試す
        $formats = array(
            'Y/m/d H:i:s',
            'Y-m-d H:i:s',
            'Y/m/d H:i',
            'Y-m-d H:i',
            'YmdHis',
            'Ymd',
        );
        
        foreach ($formats as $format) {
            $datetime = DateTime::createFromFormat($format, $datetime_string, $timezone);
            if ($datetime) {
                break;
            }
        }
        
        // それでも失敗した場合は、DateTimeのコンストラクタに任せる
        if (!$datetime) {
            $datetime = new DateTime($datetime_string, $timezone);
        }
    }
    
    return $datetime;
}

/**
 * イベントの複数の日程セットを取得
 * ACFフィールドが1〜12まで存在する場合に対応
 * events_venueはすべての日程で共通
 * 
 * @param int $post_id 投稿ID
 * @return array 日程セットの配列
 */
function get_event_schedules($post_id) {
    $schedules = array();
    
    // 共通の会場を取得（events_venue、サフィックスなし）
    $common_venue_field = get_field('events_venue', $post_id);
    $common_location = '';
    
    if ($common_venue_field) {
        if (is_array($common_venue_field)) {
            $venue_ids = $common_venue_field;
        } else {
            $venue_ids = array($common_venue_field);
        }
        
        $venue_names = array();
        foreach ($venue_ids as $venue_id) {
            $term = get_term($venue_id, 'events-venue');
            if ($term && !is_wp_error($term)) {
                $venue_names[] = $term->name;
            }
        }
        $common_location = implode(', ', $venue_names);
    }
    
    // 1〜12までのフィールドをチェック
    // ACFフィールド名は events_start_date_1, events_start_date_2, ... events_start_date_12 の形式
    for ($i = 1; $i <= 12; $i++) {
        $suffix = '_' . $i; // すべてのフィールドにサフィックスが付く（_1, _2, ..., _12）
        
        $start_date = get_field('events_start_date' . $suffix, $post_id);
        $end_date = get_field('events_end_date' . $suffix, $post_id);
        $date_text = get_field('events_date_text' . $suffix, $post_id); // 補足テキスト（iCalでは使用しない）
        
        // 開始日時が存在する場合のみ追加
        if (!empty($start_date)) {
            $schedules[] = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'date_text' => $date_text, // 補足テキスト（表示用）
                'location' => $common_location, // 共通の会場を使用
            );
        }
    }
    
    return $schedules;
}

/**
 * iCalテキストのエスケープ
 */
function escape_ical_text($text) {
    if (empty($text)) {
        return '';
    }
    
    // 改行を\\nに変換
    $text = str_replace("\r\n", '\\n', $text);
    $text = str_replace("\n", '\\n', $text);
    $text = str_replace("\r", '\\n', $text);
    
    // 特殊文字をエスケープ
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace(',', '\\,', $text);
    $text = str_replace(';', '\\;', $text);
    
    return $text;
}

/**
 * iCal形式の行を生成（75文字ごとに折り返し）
 * 
 * @param string $name フィールド名（例：SUMMARY, DESCRIPTION）
 * @param string $value 値
 * @return string フォーマットされた行
 */
function format_ical_line($name, $value) {
    if (empty($value)) {
        return '';
    }
    
    $line = $name . ':' . $value . "\r\n";
    
    // 75文字を超える場合は折り返し（最初の行はフィールド名を含むため短めに）
    if (strlen($line) > 75) {
        $lines = array();
        $current_line = $name . ':';
        $remaining = $value;
        
        while (strlen($remaining) > 0) {
            // 現在の行に追加できる最大文字数
            $max_length = 75 - strlen($current_line);
            
            if (strlen($remaining) <= $max_length) {
                // 残りが全部入る場合
                $current_line .= $remaining;
                $lines[] = $current_line . "\r\n";
                break;
            } else {
                // 折り返しが必要
                $current_line .= substr($remaining, 0, $max_length);
                $lines[] = $current_line . "\r\n";
                $remaining = substr($remaining, $max_length);
                $current_line = ' '; // 折り返し行はスペースで始まる
            }
        }
        
        return implode('', $lines);
    }
    
    return $line;
}

/**
 * Googleカレンダー用のURLを生成
 * 複数の日程がある場合は最初の日程を使用
 * 
 * @param int $post_id 投稿ID
 * @param int $schedule_index 日程のインデックス（0から始まる、デフォルトは0=最初の日程）
 * @return string GoogleカレンダーURL
 */
function get_google_calendar_url($post_id, $schedule_index = 0) {
    $post = get_post($post_id);
    if (!$post) {
        return '';
    }
    
    // イベント情報を取得
    $title = urlencode(get_the_title($post_id));
    
    // 複数の日程セットを取得
    $schedules = get_event_schedules($post_id);
    
    // 指定されたインデックスの日程を使用（存在しない場合は最初の日程）
    if (empty($schedules) || !isset($schedules[$schedule_index])) {
        $schedule_index = 0;
    }
    
    if (empty($schedules)) {
        return '';
    }
    
    $schedule = $schedules[$schedule_index];
    
    // 日時を解析（events_start_dateとevents_end_dateから直接取得）
    $start_datetime = parse_datetime_from_field($schedule['start_date']);
    $end_datetime = parse_datetime_from_field($schedule['end_date']);
    
    if (!$start_datetime) {
        return '';
    }
    
    // 終了日時がない場合は開始日時+1時間
    if (!$end_datetime) {
        $end_datetime = clone $start_datetime;
        $end_datetime->modify('+1 hour');
    }
    
    // Googleカレンダー形式の日時（YYYYMMDDTHHMMSS）
    $start_google = $start_datetime->format('Ymd\THis');
    $end_google = $end_datetime->format('Ymd\THis');
    
    // 場所を取得
    $location = urlencode($schedule['location']);
    
    // URL
    $url = urlencode(get_permalink($post_id));
    
    // 説明
    $description = wp_strip_all_tags(get_the_excerpt($post_id));
    if (empty($description)) {
        $content = get_post_field('post_content', $post_id);
        $description = wp_trim_words(wp_strip_all_tags($content), 30);
    }
    $description = urlencode($description);
    
    // GoogleカレンダーURLを生成
    $google_url = 'https://www.google.com/calendar/render?action=TEMPLATE';
    $google_url .= '&text=' . $title;
    $google_url .= '&dates=' . $start_google . '/' . $end_google;
    if (!empty($location)) {
        $google_url .= '&location=' . $location;
    }
    $google_url .= '&details=' . $description;
    $google_url .= '&sf=true&output=xml';
    
    return $google_url;
}

/**
 * iCalファイルのURLを生成
 * 
 * @param int $post_id 投稿ID
 * @return string iCalファイルURL
 */
function get_ical_file_url($post_id) {
    return add_query_arg(array(
        'ical_download' => '1',
        'post_id' => $post_id
    ), home_url('/'));
}

/**
 * iCalダウンロードのリクエストを処理
 */
add_action('init', function() {
    if (isset($_GET['ical_download']) && isset($_GET['post_id'])) {
        generate_ical_file();
    }
});

