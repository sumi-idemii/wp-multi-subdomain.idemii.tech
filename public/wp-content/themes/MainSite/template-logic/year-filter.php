<?php
/**
 * 年別絞り込みの共通処理
 */

/**
 * 投稿の年リストを取得
 * 
 * @param string $post_type 投稿タイプ
 * @param array $query_args クエリ引数（オプション）
 * @return array 年の配列
 */
function get_post_years($post_type, $query_args = []) {
    global $wpdb;
    
    // articlesとnewsの場合は投稿日で年を取得
    if ($post_type === 'articles' || $post_type === 'news') {
        $query = "
            SELECT DISTINCT YEAR(p.post_date) as year
            FROM {$wpdb->posts} p
            WHERE p.post_type = %s
            AND p.post_status = 'publish'
        ";
        $params = [$post_type];
    } else {
        // collectionとeventsの場合はmeta_valueから年を取得
        if ($post_type === 'collection') {
            $meta_key = 'collection_start_date';
        } else if ($post_type === 'events') {
            $meta_key = 'events_start_date';
        }

        $query = "
            SELECT DISTINCT YEAR(meta_value) as year
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status = 'publish'
            AND pm.meta_key = %s
        ";
        $params = [$post_type, $meta_key];
    }
    
    // タクソノミーの条件がある場合
    if (!empty($query_args['tax_query'])) {
        foreach ($query_args['tax_query'] as $tax_query) {
            // fieldが'slug'の場合はスラッグで検索、それ以外はterm_idで検索
            $field = isset($tax_query['field']) ? $tax_query['field'] : 'term_id';
            
            if ($field === 'slug') {
                $query .= "
                    AND EXISTS (
                        SELECT 1
                        FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                        WHERE tr.object_id = p.ID
                        AND tt.taxonomy = %s
                        AND t.slug = %s
                    )
                ";
                $params[] = $tax_query['taxonomy'];
                $params[] = $tax_query['terms'];
            } else {
                $query .= "
                    AND EXISTS (
                        SELECT 1
                        FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                        WHERE tr.object_id = p.ID
                        AND tt.taxonomy = %s
                        AND t.term_id = %d
                    )
                ";
                $params[] = $tax_query['taxonomy'];
                $params[] = $tax_query['terms'];
            }
        }
    }
    
    $query .= " ORDER BY year DESC";
    
    // $wpdb->prepareは配列を直接受け取れないため、可変長引数として展開
    if (!empty($params)) {
        return $wpdb->get_col($wpdb->prepare($query, ...$params));
    } else {
        return $wpdb->get_col($query);
    }
}
