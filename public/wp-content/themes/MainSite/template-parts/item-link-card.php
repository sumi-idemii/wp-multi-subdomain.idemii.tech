<?php
/**
 * リンクカード表示用パーツ
 * 
 * 記事やイベントなどのコンテンツをカード形式で表示するテンプレートパーツです。
 * サムネイル画像、タイトル、日付、学部情報を含むカードデザインを生成します。
 * 
 * @package Nagoya-U-En
 * @subpackage Template-Parts
 * 
 * @param array $args {
 *     カードの設定パラメータ
 * 
 *     @type string  $title      カードのタイトル（デフォルト: get_the_title()）
 *     @type string  $link       リンク先URL（デフォルト: get_the_permalink()）
 *     @type string  $date       公開日（デフォルト: YYYY/MM/DD形式の日付）
 *     @type string  $thumbnail  サムネイル画像のURL（デフォルト: get_the_post_thumbnail_url()）
 *     @type string  $type       コンテンツタイプ（'articles'、'events'または'collection'）
 * }
 * 
 * @example
 * // 基本的な使用方法
 * get_template_part('template-parts/item', 'link-card', [
 *     'title' => 'カードのタイトル',
 *     'link' => 'https://example.com/article',
 *     'date' => '2025/04/24',
 *     'thumbnail' => 'https://example.com/image.jpg',
 *     'type' => 'articles'
 * ]);
 * 
 * @note
 * - 日付はYYYY/MM/DD形式で表示されます
 * - イベントとコレクションの場合は開始日と終了日を「YYYY/MM/DD–YYYY/MM/DD」形式で表示
 * - サムネイルが未設定の場合、デフォルトの画像が使用されます
 * - typeが'articles'の場合、articles_categoryの値が表示されます
 * - typeが'events'の場合、固定値の「Events」が表示され、イベントタイプも表示されます
 * - typeが'collection'の場合、collection_categoryの値が表示されます
 * - カードにはホバーエフェクトが実装されています
 * - data-link-card="root"属性はJavaScript制御用に使用されます
 * - アイコンとホバーエフェクトはコンポーネントに組み込まれています
 */

$current_locale = get_locale();

switch_to_locale('en_US');
$args = wp_parse_args($args, [
    'title' => get_the_title(),
    'link' => get_the_permalink(),
    'date' => date_i18n('Y/m/d', strtotime(get_the_date('Y-m-d'))),
    'thumbnail' => get_the_post_thumbnail_url(),
    'type' => 'articles'
]);
switch_to_locale($current_locale);

if(!$args['thumbnail']){
    $args['thumbnail'] = '/assets/img/pages/news/img-news-thumb-default.webp';
}

// typeに応じて表示するテキストを設定
$display_category_list = [];
$event_type_display = '';
$display_date = $args['date'];

if ($args['type'] === 'events') {
    $display_category_list[] = [
        'text' => 'Events',
        'link' => '#'
    ];
    // イベントタイプの表示名を取得
    $event_type = get_field('events_type');
    $event_type_labels = [
        'online' => 'Online',
        'in_person' => 'In-person',
        'hybrid' => 'Hybrid'
    ];
    $event_type_display = isset($event_type_labels[$event_type]) ? $event_type_labels[$event_type] : '';

    // イベントの日付範囲を取得
    $start_date = get_field('events_start_date');
    $end_date = get_field('events_end_date');
    if ($start_date) {
        if ($end_date) {
            $display_date = date_i18n('Y/m/d', strtotime($start_date)) . '–' . date_i18n('Y/m/d', strtotime($end_date));
        } else {
            $display_date = date_i18n('Y/m/d', strtotime($start_date));
        }
    }
} elseif ($args['type'] === 'collection') {
    $collection_categories = get_the_terms(get_the_ID(), 'collection_category');
    if ($collection_categories && !is_wp_error($collection_categories)) {
        foreach ($collection_categories as $category) {
            $display_category_list[] = [
                'text' => $category->name,
                'link' => get_term_link($category)
            ];
        }
    }

    // コレクションの日付範囲を取得
    $start_date = get_field('collection_start_date');
    $end_date = get_field('collection_end_date');
    if ($start_date) {
        if ($end_date) {
            $display_date = date_i18n('Y/m/d', strtotime($start_date)) . '–' . date_i18n('Y/m/d', strtotime($end_date));
        } else {
            $display_date = date_i18n('Y/m/d', strtotime($start_date));
        }
    }
} elseif ($args['type'] === 'articles') {
    $articles_category = get_the_terms(get_the_ID(), 'articles_category');
    $articles_type = get_the_terms(get_the_ID(), 'articles_type');
    if ($articles_category && $articles_type) {
        $categories = array_merge($articles_category, $articles_type);
    } else {
        $categories = $articles_category ? $articles_category : $articles_type;
    }

    if ($categories) {
        foreach ($categories as $category) {
            $display_category_list[] = [
                'text' => $category->name,
                'link' => get_term_link($category)
            ];
        }
    }
}
?>

<div class="c-linkCard -fixed-title-height" data-link-card="root"><a class="c-linkCard-link" href="<?= $args['link']; ?>">
    <div class="c-linkCard-thumb">
        <div class="c-linkCard-thumb-image"><img src="<?= $args['thumbnail'] ?>" alt="<?= $args['title'] ?>"><span class="c-linkCard-thumb-decoration"><span class="c-hoverBackgroundShineCircle"><span class="background"></span><span class="shine"></span></span><span class="c-linkCard-thumb-decoration-icon icon-arrow-right"></span></span></div>
    </div><span class="c-hoverTextGradientSlide c-linkCard-title"><?= $args['title'] ?></span></a>
    <div class="c-linkCard-info">
        <div class="c-linkCard-desc">
            <time class="c-linkCard-date"><?= $display_date ?></time>
            <?php if ($args['type'] === 'events' && !empty($event_type_display)): ?>
            <span class="c-linkCard-type"><?= esc_html($event_type_display) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($display_category_list)): ?>
            <?php foreach ($display_category_list as $category): ?><span class="c-hoverTextGradientSlide c-linkCard-tag"><?php if ($category['link'] !== '#'): ?><a class="c-linkCard-tag-link" href="<?= $category['link']; ?>"><?= $category['text']; ?></a><?php else: ?><span class="c-linkCard-tag-link"><?= $category['text']; ?></span><?php endif; ?></span><?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>