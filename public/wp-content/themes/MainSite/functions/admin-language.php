<?php
/**
 * 管理画面の言語を日本語に設定
 */

/**
 * 管理画面のロケールを日本語に強制設定
 * すべてのユーザーの管理画面を日本語で表示
 */
function force_admin_locale_japanese($locale) {
    // 管理画面の場合のみ日本語に設定
    if (is_admin()) {
        return 'ja';
    }
    return $locale;
}
add_filter('locale', 'force_admin_locale_japanese', 1);

/**
 * ユーザーのロケール設定を日本語に強制
 * ユーザープロフィールの言語設定を無視して日本語に設定
 */
function force_user_locale_japanese($locale, $user_id) {
    // 管理画面の場合のみ日本語に設定
    if (is_admin()) {
        return 'ja';
    }
    return $locale;
}
add_filter('get_user_locale', 'force_user_locale_japanese', 1, 2);

/**
 * サイトのロケールを日本語に設定
 */
function force_site_locale_japanese($locale) {
    // 管理画面の場合のみ日本語に設定
    if (is_admin()) {
        return 'ja';
    }
    return $locale;
}
add_filter('determine_locale', 'force_site_locale_japanese', 1);

