<?php
/**
 * 年別フィルターテンプレート
 * 
 * @param array $args {
 *     @type array  $filter_list     年リストの配列
 *     @type string $selected_value  選択されている年の値
 *     @type string $post_tag        カテゴリータグ（オプション）
 * }
 */

$filter_list = isset($args['filter_list']) ? $args['filter_list'] : [];
$selected_value = isset($args['selected_value']) ? $args['selected_value'] : '';
$post_tag = isset($args['post_tag']) ? $args['post_tag'] : '';

// 年リストが空の場合は何も表示しない
if (empty($filter_list)) {
    return;
}

// 現在のURLを取得
$current_url = home_url($_SERVER['REQUEST_URI']);
$url_parts = parse_url($current_url);
$base_url = $url_parts['path'];
$query_params = [];
if (isset($url_parts['query'])) {
    parse_str($url_parts['query'], $query_params);
}

// フィルター用のURLを構築
$filter_url = $base_url;
if ($post_tag) {
    $query_params['news'] = $post_tag;
}
?>

<div class="c-filter">
    <form method="get" action="<?php echo esc_url($filter_url); ?>" class="c-filter-form">
        <?php if ($post_tag): ?>
            <input type="hidden" name="news" value="<?php echo esc_attr($post_tag); ?>">
        <?php endif; ?>
        
        <select name="filter" class="c-filter-select" onchange="this.form.submit()">
            <option value="">すべての年</option>
            <?php foreach ($filter_list as $year): ?>
                <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_value, $year); ?>>
                    <?php echo esc_html($year); ?>年
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>


