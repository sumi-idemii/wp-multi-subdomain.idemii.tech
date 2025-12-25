<?php
/**
 * タクソノミーの登録・削除処理
 *
 * @package wp-multi-subdomain.idemii.tech
 */

/**
 * announcements_categoryタクソノミーを削除
 */
function unregister_announcements_category_taxonomy() {
    // タクソノミーが存在する場合、登録を解除
    if (taxonomy_exists('announcements_category')) {
        unregister_taxonomy('announcements_category');
    }
    
    // notice_categoryも念のため削除（旧名称）
    if (taxonomy_exists('notice_category')) {
        unregister_taxonomy('notice_category');
    }
}
add_action('init', 'unregister_announcements_category_taxonomy', 20);

/**
 * news_categoryタクソノミーを削除
 */
function unregister_news_category_taxonomy() {
    // タクソノミーが存在する場合、登録を解除
    if (taxonomy_exists('news_category')) {
        unregister_taxonomy('news_category');
    }
}
add_action('init', 'unregister_news_category_taxonomy', 20);

/**
 * events_venueタクソノミーを削除
 */
function unregister_events_venue_taxonomy() {
    // タクソノミーが存在する場合、登録を解除
    if (taxonomy_exists('events_venue')) {
        unregister_taxonomy('events_venue');
    }
    
    // events-venueも念のため削除（ハイフン形式）
    if (taxonomy_exists('events-venue')) {
        unregister_taxonomy('events-venue');
    }
}
add_action('init', 'unregister_events_venue_taxonomy', 20);

/**
 * events_target_visitorsタクソノミーを削除
 */
function unregister_events_target_visitors_taxonomy() {
    // タクソノミーが存在する場合、登録を解除
    if (taxonomy_exists('events_target_visitors')) {
        unregister_taxonomy('events_target_visitors');
    }
    
    // events-target-visitorsも念のため削除（ハイフン形式）
    if (taxonomy_exists('events-target-visitors')) {
        unregister_taxonomy('events-target-visitors');
    }
}
add_action('init', 'unregister_events_target_visitors_taxonomy', 20);

