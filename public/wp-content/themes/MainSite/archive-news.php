<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package nagoya-u-en
 */

$post_tag = isset($_GET['news']) ? sanitize_text_field($_GET['news']) : '';
$selected_year = isset($_GET['filter']) ? $_GET['filter'] : '';

// 初期値が選択された場合、カテゴリーのトップページにリダイレクト
if (isset($_GET['filter']) && $_GET['filter'] === '') {
    if ($post_tag) {
        wp_redirect(home_url('/news/articles/?post_tag=' . $post_tag));
    } else {
        wp_redirect(home_url('/news/articles/'));
    }
    exit;
}

// 現在のタクソノミー名を取得（タイトル表示用）
$current_taxonomy = '';
if ($post_tag) {
    if ($post_tag) {
        $term = get_term_by('slug', $post_tag, 'news_category');
    }

    if ($term && !is_wp_error($term)) {
        $current_taxonomy = $term->name;
    }
}

// 共通処理の読み込み
require_once get_template_directory() . '/template-logic/year-filter.php';

// 年リストを取得
$years_args = [];
if ($post_tag) {
    $years_args['tax_query'] = [
        [
            'taxonomy' => 'news_category',
            'field' => 'slug',
            'terms' => $post_tag
        ]
    ];
}
$years = get_post_years('news', $years_args);

// メインクエリを上書き
$args = [
    'post_type' => 'news',
    'posts_per_page' => PER_PAGE_ARTICLES,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
];
// タグによる絞り込みがある場合
if ($post_tag) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'news_category',
            'field' => 'slug',
            'terms' => $post_tag
        ]
    ];
}
// 年の絞り込みがある場合
if ($selected_year !== '') {
    $args['date_query'] = [
        [
            'year' => $selected_year
        ]
    ];
}

// メインクエリを上書き
global $wp_query;
$wp_query = new WP_Query($args);

get_header();
?>

<div class="l-default-content" id="content">
    <main class="p-news-article-index" data-page-news-article-index="root">
        <?php get_template_part('template-parts/item', 'kv', [
            'title' => ($current_taxonomy) ? $current_taxonomy . '-Related News' : 'News',
        ]); ?>
        <div class="p-news-events-index-contents" data-page-news-events-index="content" data-page="content">
            <section class="c-blockSection">
                <div class="c-blockSection-inner">
                    <?php get_template_part('template-parts/filter', null, [
                        'filter_list' => $years,
                        'selected_value' => $selected_year,
                        'post_tag' => $post_tag,
                    ]); ?>
                    <div class="c-listLinkCard -cols-3 -cols-1-sp">
                        <div class="c-column -cols-3 -cols-1-sp">
                        <?php if (have_posts()): 
                            while (have_posts()): 
                                the_post();
                                get_template_part('template-parts/item', 'link-card', [
                                    'type' => 'news'
                                ]);
                            endwhile;
                        endif; ?>
                        </div>
                    </div>
                    <?php if (get_the_posts_pagination()): ?>
                        <?php get_template_part('template-parts/pagination'); ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        </main>
        <?php include(get_template_directory() . '/template-parts/cookie-banner.php'); ?>
    </div>

<?php
get_footer();
