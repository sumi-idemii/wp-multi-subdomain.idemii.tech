<?php
/**
 * 翻訳機能
 * Polylangを使用した多言語対応
 */

/**
 * Polylangに翻訳文字列を登録
 * 管理画面の「言語」→「文字列」に表示される
 */
function register_event_translations() {
    // Polylangが有効な場合のみ実行
    if (!function_exists('pll_register_string')) {
        return;
    }
    
    // イベントページ用の翻訳文字列を登録
    $translations = array(
        'venue' => '会場',
        'target_visitors' => '対象参加者',
        'language' => '使用言語',
        'fee' => '費用',
        'team' => '運営チーム',
        'date_text' => 'イベント開催テキスト入力',
        'event_date' => '開催日時',
        'add_to_calendar' => 'カレンダーに追加',
    );
    
    // 各文字列をPolylangに登録
    // 第1引数: 文字列名（識別子）、第2引数: 翻訳する文字列、第3引数: 文字列グループ
    foreach ($translations as $key => $string) {
        pll_register_string('event_' . $key, $string, 'Event Page Translations');
    }
}
// initアクションで文字列を登録（Polylangが初期化された後）
add_action('init', 'register_event_translations', 20);

/**
 * 翻訳文字列を取得
 * Polylangが有効な場合はpll__()を使用、無効な場合は元の文字列を返す
 * 
 * @param string $string 翻訳する文字列
 * @param string $name 翻訳文字列の名前（Polylangの文字列翻訳で使用）
 * @return string 翻訳された文字列
 */
function get_translated_string($string, $name = '') {
    // Polylangが有効な場合
    if (function_exists('pll__')) {
        // 名前が指定されている場合はそれを使用、ない場合は文字列自体を使用
        $translation_name = !empty($name) ? $name : sanitize_key($string);
        return pll__($string);
    }
    
    // Polylangが無効な場合は元の文字列を返す
    return $string;
}

/**
 * 現在の言語コードを取得
 * 
 * @return string 言語コード（例: 'ja', 'en'）
 */
function get_current_language_code() {
    if (function_exists('pll_current_language')) {
        return pll_current_language();
    }
    
    // Polylangが無効な場合はデフォルト言語を返す
    return get_locale() === 'ja' ? 'ja' : 'en';
}

/**
 * イベントページ用の翻訳文字列を取得
 * Polylangの文字列翻訳機能を使用（管理画面から翻訳可能）
 * 
 * @param string $key 翻訳キー
 * @return string 翻訳された文字列
 */
function get_event_translation($key) {
    // 翻訳キーとデフォルト文字列のマッピング
    $default_translations = array(
        'venue' => '会場',
        'target_visitors' => '対象参加者',
        'language' => '使用言語',
        'fee' => '費用',
        'team' => '運営チーム',
        'date_text' => 'イベント開催テキスト入力',
        'event_date' => '開催日時',
        'add_to_calendar' => 'カレンダーに追加',
    );
    
    // デフォルト文字列を取得
    $default_string = isset($default_translations[$key]) ? $default_translations[$key] : $key;
    
    // Polylangが有効な場合はpll__()を使用（管理画面から翻訳可能）
    if (function_exists('pll__')) {
        return pll__($default_string);
    }
    
    // Polylangが無効な場合は、言語に応じてデフォルト文字列を返す
    $lang = get_current_language_code();
    
    // 英語の翻訳（Polylangが無効な場合のフォールバック）
    $english_translations = array(
        'venue' => 'Venue',
        'target_visitors' => 'Target Participants',
        'language' => 'Language Used',
        'fee' => 'Fee',
        'team' => 'Operating Team',
        'date_text' => 'Event Holding Text Input',
        'event_date' => 'Event Date',
        'add_to_calendar' => 'Add to Calendar',
    );
    
    if ($lang === 'en' && isset($english_translations[$key])) {
        return $english_translations[$key];
    }
    
    // デフォルトは日本語
    return $default_string;
}

/**
 * 日付表示形式を言語に応じて変更
 * 
 * @param DateTime $datetime 日時オブジェクト
 * @param string $format_type フォーマットタイプ（'start' または 'end'）
 * @return string フォーマットされた日付文字列
 */
function format_event_date_by_language($datetime, $format_type = 'start') {
    $lang = get_current_language_code();
    
    if ($lang === 'en') {
        // 英語の場合
        if ($format_type === 'start') {
            return $datetime->format('F j, Y g:i A');
        } else {
            return $datetime->format('g:i A');
        }
    } else {
        // 日本語の場合
        $year = (int)$datetime->format('Y');
        $month = (int)$datetime->format('m');
        $day = (int)$datetime->format('d');
        $time = $datetime->format('H:i');
        
        return $year . '年' . $month . '月' . $day . '日 ' . $time;
    }
}

/**
 * 日付範囲の表示形式を言語に応じて変更
 * 
 * @param DateTime $start_datetime 開始日時
 * @param DateTime $end_datetime 終了日時
 * @return array 開始日時と終了日時の表示文字列
 */
function format_event_date_range_by_language($start_datetime, $end_datetime) {
    $lang = get_current_language_code();
    
    if ($lang === 'en') {
        // 英語の場合
        $start_year = (int)$start_datetime->format('Y');
        $start_month = (int)$start_datetime->format('m');
        $start_day = (int)$start_datetime->format('d');
        $start_time = $start_datetime->format('g:i A');
        
        $end_year = (int)$end_datetime->format('Y');
        $end_month = (int)$end_datetime->format('m');
        $end_day = (int)$end_datetime->format('d');
        $end_time = $end_datetime->format('g:i A');
        
        // 条件1: 同じ日の場合
        if ($start_year === $end_year && $start_month === $end_month && $start_day === $end_day) {
            $start_display = $start_datetime->format('F j, Y') . ' ' . $start_time;
            $end_display = $end_time;
        }
        // 条件2: 同じ月で日が異なる場合
        elseif ($start_year === $end_year && $start_month === $end_month) {
            $start_display = $start_datetime->format('F j') . ' ' . $start_time;
            $end_display = $end_datetime->format('j') . ' ' . $end_time;
        }
        // 条件3: 月が異なる場合
        else {
            $start_display = $start_datetime->format('F j') . ' ' . $start_time;
            $end_display = $end_datetime->format('F j') . ' ' . $end_time;
        }
        
        return array(
            'start' => $start_display,
            'end' => $end_display
        );
    } else {
        // 日本語の場合
        $start_year = (int)$start_datetime->format('Y');
        $start_month = (int)$start_datetime->format('m');
        $start_day = (int)$start_datetime->format('d');
        $start_time = $start_datetime->format('H:i');
        
        $start_display = $start_year . '年' . $start_month . '月' . $start_day . '日 ' . $start_time;
        
        $end_year = (int)$end_datetime->format('Y');
        $end_month = (int)$end_datetime->format('m');
        $end_day = (int)$end_datetime->format('d');
        $end_time = $end_datetime->format('H:i');
        
        // 条件1: 同じ日の場合
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
        
        return array(
            'start' => $start_display,
            'end' => $end_display
        );
    }
}

