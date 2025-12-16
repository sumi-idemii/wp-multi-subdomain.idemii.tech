<?php
/**
 * ページネーション表示用パーツ
 * 
 * WordPressのページネーションを表示するためのテンプレートパーツです。
 * 前へ/次へボタン、ページ番号、省略記号を含むページネーションを生成します。
 * 
 * @package Nagoya-U-En
 * @subpackage Template-Parts
 * 
 * @param array $args {
 *     ページネーションの設定パラメータ
 * 
 *     @type WP_Query $query ページネーションを表示するクエリオブジェクト
 * }
 */

$args = wp_parse_args($args, [
    'query' => $GLOBALS['wp_query']
]);

if ($args['query']->max_num_pages <= 1) {
    return;
}
?>

<div class="c-pagination" data-pagination="root">
    <div class="pagination">
        <?php
        $pagination = paginate_links([
            'base' => str_replace(999999999, '%#%', get_pagenum_link(999999999)),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $args['query']->max_num_pages,
            'prev_text' => '<span class="c-hoverBackgroundShineCircle"><span class="background"></span><span class="shine"></span></span><div class="prev-icon icon-arrow-left" aria-label="Go to previous project"></div>',
            'next_text' => '<span class="c-hoverBackgroundShineCircle"><span class="background"></span><span class="shine"></span></span><div class="next-icon icon-arrow-right" aria-label="Go to next project"></div>',
            'type' => 'array',
            'end_size' => 1,
            'mid_size' => 1
        ]);

        if ($pagination) {
            foreach ($pagination as $key => $page_link) {
                // URLの修正処理（#038;とpost_tag重複の解決）
                $page_link = str_replace('#038;', '&', $page_link);
                
                // post_tagの重複を除去（より安全な方法）
                if (preg_match('/href="([^"]+)"/', $page_link, $url_matches)) {
                    $url = $url_matches[1];
                    
                    // URLをパースしてpost_tagパラメータを処理
                    $parsed_url = parse_url($url);
                    if (isset($parsed_url['query'])) {
                        parse_str($parsed_url['query'], $query_params);
                        
                        // post_tagが重複している場合は最初のものだけ残す
                        if (isset($query_params['post_tag']) && is_array($query_params['post_tag'])) {
                            $query_params['post_tag'] = $query_params['post_tag'][0];
                        }
                        
                        // クエリパラメータを再構築
                        $new_query = http_build_query($query_params);
                        $new_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
                        if (isset($parsed_url['port'])) {
                            $new_url .= ':' . $parsed_url['port'];
                        }
                        $new_url .= $parsed_url['path'];
                        if (!empty($new_query)) {
                            $new_url .= '?' . $new_query;
                        }
                        
                        // 修正されたURLでリンクを更新
                        $page_link = str_replace($url, $new_url, $page_link);
                    }
                }

                // 前へ/次へボタンのクラスを追加
                if (strpos($page_link, 'prev') !== false) {
                    $page_link = str_replace('page-numbers', 'prev page-numbers', $page_link);
                } elseif (strpos($page_link, 'next') !== false) {
                    $page_link = str_replace('page-numbers', 'next page-numbers', $page_link);
                }
                
                // 現在のページのクラスを追加
                if (strpos($page_link, 'current') !== false) {
                    $page_link = str_replace('page-numbers', 'page-numbers current', $page_link);
                    $page_link = str_replace('<span', '<span aria-current="page"', $page_link);
                }

                // 省略記号のクラスを追加
                if (strpos($page_link, 'dots') !== false) {
                    $page_link = str_replace('page-numbers', 'page-numbers dots', $page_link);
                }

                echo $page_link;
            }
        }
        ?>
    </div>
</div> 