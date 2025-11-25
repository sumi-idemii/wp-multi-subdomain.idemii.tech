<?php

function get_breadcrumb() {
    $breadcrumb = array();

    // トップページ
    $breadcrumb[] = array(
        'url' => home_url(),
        'text' => 'Home'
    );

    if (is_search()) {
        $breadcrumb[] = array(
            'url' => get_search_link(),
            'text' => 'Search'
        );
    } elseif (is_category() || is_single()) {
        // カテゴリーの場合
        if (is_category()) {
            $cat = get_queried_object();
            $breadcrumb[] = array(
                'url' => get_category_link($cat->term_id),
                'text' => $cat->name
            );
        }
        // 投稿の場合
        if (is_single()) {
            $post_type = get_post_type();
            if ($post_type) {
                // カスタム投稿タイプの場合、Newsセクションを追加
                if ($post_type !== 'post') {
                    $breadcrumb[] = get_news_breadcrumb();
                }

                $post_type_obj = get_post_type_object($post_type);
                $breadcrumb[] = array(
                    'url' => get_post_type_archive_link($post_type),
                    'text' => $post_type_obj->labels->name
                );
            }
            $categories = get_the_category();
            if (!empty($categories)) {
                $cat = $categories[0];
                $breadcrumb[] = array(
                    'url' => get_category_link($cat->term_id),
                    'text' => $cat->name
                );
            }
            $breadcrumb[] = array(
                'url' => get_permalink(),
                'text' => get_the_title()
            );
        }
    } elseif (is_archive()) {
        // Newsセクションのトップページを追加
        $breadcrumb[] = get_news_breadcrumb();

        // アーカイブページの場合
        if (is_tax()) {
            // タクソノミーの場合
            $term = get_queried_object();
            $taxonomy = get_taxonomy($term->taxonomy);

            if ($term->taxonomy === 'researchers' || $term->taxonomy === 'sdgs') {
                // 固定ページでテンプレート指定しているページが親にいる場合
                $researchers_page = get_page_by_path('news/' . $term->taxonomy);
                if ($researchers_page) {
                    $breadcrumb[] = array(
                        'url' => get_permalink($researchers_page->ID),
                        'text' => $researchers_page->post_title
                    );
                }

                // sdgsタクソノミーの場合はdescriptionを返す
                // 改修箇所 sdgsタクソノミーの場合はdescriptionを返す
                if ($term->taxonomy === 'sdgs') {
                    $breadcrumb[] = array(
                        'url' => get_term_link($term),
                        'text' => $term->description
                    );
                }

            } else if ($term->taxonomy === 'articles_category' || $term->taxonomy === 'articles_type') {
                // 記事のアーカイブページを追加
                $post_type_obj = get_post_type_object('articles');
                $breadcrumb[] = array(
                    'url' => get_post_type_archive_link('articles'),
                    'text' => $post_type_obj->labels->name
                );

                $breadcrumb[] = array(
                    'url' => get_term_link($term),
                    'text' => $term->name
                );

            } else {
                // その他のタクソノミーの場合
                $post_type = $taxonomy->object_type[0];
                $post_type_obj = get_post_type_object($post_type);
                $breadcrumb[] = array(
                    'url' => get_post_type_archive_link($post_type),
                    'text' => $post_type_obj->labels->name
                );

                if ($post_type === 'collection') {
                    $breadcrumb[] = array(
                        'url' => get_term_link($term),
                        'text' => $term->name
                    );
                }
            }
        } else {
            // 通常のアーカイブページの場合
            $post_type = get_post_type();
            if ($post_type) {
                $post_type_obj = get_post_type_object($post_type);
                $breadcrumb[] = array(
                    'url' => get_post_type_archive_link($post_type),
                    'text' => $post_type_obj->labels->name
                );
            }
        }
    } elseif (is_page()) {
        // 固定ページの場合
        $post = get_post();
        if ($post->post_parent) {
            $ancestors = array_reverse(get_post_ancestors($post->ID));
            foreach ($ancestors as $ancestor) {
                $ancestor_page = get_post($ancestor);
                // サイトページトップは表示しない
                if ($ancestor_page->post_name === 'site') {
                    continue;
                }

                $breadcrumb[] = array(
                    'url' => get_permalink($ancestor),
                    'text' => get_the_title($ancestor)
                );
            }
        }

        if (is_page('events_archive')) {
            $breadcrumb[] = array(
                'url' => home_url('/news/events/'),
                'text' => 'Events'
            );
            $breadcrumb[] = array(
                'url' => get_permalink(),
                'text' => 'Past events'
            );
        } else {
            // クエリをリセットしてから情報を取得
            wp_reset_postdata();
            
            $breadcrumb[] = array(
                'url' => get_permalink(),
                'text' => get_the_title()
            );
        }
    } elseif (is_404()) {
        $breadcrumb[] = array(
            'url' => home_url(),
            'text' => '404'
        );
    }

    return $breadcrumb;
}

// パンくずリストを表示
function display_breadcrumb() {
    // アセットファイルのリクエストはスキップ
    if (strpos($_SERVER['REQUEST_URI'], '/assets/') === 0) {
        return;
    }
    
    $breadcrumb = get_breadcrumb();
    echo '<nav class="l-theBreadcrumb">';
    echo '<div class="l-theBreadcrumb-inner">';
    echo '<ol class="l-theBreadcrumb-list">';

    foreach ($breadcrumb as $index => $item) {
        $page_text = esc_html($item['text']);

        echo '<li class="l-theBreadcrumb-item">';
        if ($index === count($breadcrumb) - 1) {
            echo '<p>' . $page_text . '</p>';
        } else {
            echo '<a href="' . esc_url($item['url']) . '">' . $page_text . '</a>';
        }
        echo '</li>';
    }

    echo '</ol>';
    echo '</div>';
    echo '</nav>';
}

// パンくず追加用にNewsページの情報取得
function get_news_breadcrumb() {
    $breadcrumb = array();
    $news_page = get_page_by_path('news');
    if ($news_page) {
        $breadcrumb = array(
            'url' => get_permalink($news_page->ID),
            'text' => $news_page->post_title
        );
    }
    return $breadcrumb;
}


// <nav class="l-theBreadcrumb">
//     <div class="l-theBreadcrumb-inner">
//     <ol class="l-theBreadcrumb-list">
//         <li class="l-theBreadcrumb-item"><a href="/"><span class="c-hoverTextGradientSlide">Home</span></a>
//         </li>
//         <li class="l-theBreadcrumb-item">
//         <p>★★★★★</p>
//         </li>
//     </ol>
//     </div>
// </nav>
