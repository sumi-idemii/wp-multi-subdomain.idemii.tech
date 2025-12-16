<?php
$current_locale = get_locale();

switch_to_locale('en_US');
$args = wp_parse_args($args, [
    'title' => get_the_title(),
    'link' => get_the_permalink(),
    'date' => date_i18n('Y/m/d', strtotime(get_the_date('Y-m-d'))),
    'thumbnail' => get_the_post_thumbnail_url(),
    'type' => get_post_type()
]);
switch_to_locale($current_locale);

if(!$args['thumbnail']){
    $args['thumbnail'] = '/assets/img/pages/news/img-news-thumb-default.webp';
}

// カテゴリー情報の取得
if ($args['type'] === 'articles') {
    $articles_category = get_the_terms(get_the_ID(), 'articles_category');
    $articles_type = get_the_terms(get_the_ID(), 'articles_type');
    if ($articles_category && $articles_type) {
        $categories = array_merge($articles_category, $articles_type);
    } else {
        $categories = $articles_category ? $articles_category : $articles_type;
    }
} elseif ($args['type'] === 'collection') {
    $categories = get_the_terms(get_the_ID(), 'collection_category');
}

// typeに応じて表示するテキストを設定
$event_type_display = '';
$display_date = $args['date'];

if ($args['type'] === 'events') {
    // イベントタイプの表示名を取得
    $event_type = get_field('events_type');
    $event_type_labels = [
        'online' => 'Online',
        'in_person' => 'In-person',
        'hybrid' => 'Hybrid'
    ];
    $event_type_display = isset($event_type_labels[$event_type]) ? $event_type_labels[$event_type] : '';
}

if ($args['type'] === 'events' || $args['type'] === 'collection') {
    // 日付範囲を取得
    $start_date = get_field($args['type'] . '_start_date');
    $end_date = get_field($args['type'] . '_end_date');
    if ($start_date && $end_date) {
        $display_date = date_i18n('Y/m/d', strtotime($start_date)) . '–' . date_i18n('Y/m/d', strtotime($end_date));
    } else {
        $display_date = date_i18n('Y/m/d', strtotime($start_date));
    }
}
?>
<li class="swiper-slide">
    <div class="c-linkCard" data-link-card="root">
        <a class="c-linkCard-link" href="<?= $args['link']; ?>">
            <div class="c-linkCard-thumb">
                <div class="c-linkCard-thumb-image"><img src="<?= $args['thumbnail']; ?>" alt="<?= $args['title']; ?>"><span class="c-linkCard-thumb-decoration"><span class="c-hoverBackgroundShineCircle"><span class="background"></span><span class="shine"></span></span><span class="c-linkCard-thumb-decoration-icon icon-arrow-right"></span></span></div>
            </div><span class="c-hoverTextGradientSlide c-linkCard-title"><?= $args['title'] ?></span>
        </a>
        <div class="c-linkCard-info">
            <div class="c-linkCard-desc">
                <time class="c-linkCard-date"><?= $display_date; ?></time>
                <?php if ($args['type'] === 'events' && !empty($event_type_display)): ?>
                <span class="c-linkCard-type"><?= esc_html($event_type_display) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($args['type'] === 'events'): ?>
            <span class="c-hoverTextGradientSlide c-linkCard-tag"><a class="c-linkCard-tag-link" href="/news/events/">Events</a></span>
            <?php elseif (!empty($categories) && !is_wp_error($categories)): ?>
                <?php foreach ($categories as $index => $category): ?><span class="c-hoverTextGradientSlide c-linkCard-tag"><a class="c-linkCard-tag-link" href="<?= get_term_link($category); ?>"><?= $category->name; ?></a></span><?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</li>