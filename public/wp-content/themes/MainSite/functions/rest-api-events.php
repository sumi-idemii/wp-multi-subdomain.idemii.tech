<?php
/**
 * REST API - Events投稿タイプ関連の処理
 */

/**
 * events投稿タイプのREST APIレスポンスでorganisationタクソノミーにcolorを追加
 * ターム名の配列になっている場合も処理する（優先度30で最後に実行）
 */
function add_color_to_organisation_in_rest_api($data, $post, $request) {
    // events投稿タイプの場合のみ実行
    if ($post->post_type !== 'events') {
        return $data;
    }

    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field')) {
        return $data;
    }

    // organisationタクソノミーが存在する場合
    if (isset($data->data['organisation']) && !empty($data->data['organisation'])) {
        $organisation = $data->data['organisation'];
        $term_objects = array();

        // 既にタームオブジェクトの配列になっている場合はスキップ
        if (is_array($organisation) && !empty($organisation) && isset($organisation[0]) && is_array($organisation[0]) && isset($organisation[0]['id'])) {
            // 既にタームオブジェクト形式なので、そのまま返す
            return $data;
        }

        // ターム名の配列の場合
        if (is_array($organisation)) {
            foreach ($organisation as $term_value) {
                $term = null;
                
                // 数値の場合はタームIDとして処理
                if (is_numeric($term_value)) {
                    $term = get_term($term_value, 'organisation');
                } else {
                    // 文字列の場合はターム名として処理（ターム名からタームIDを逆引き）
                    $term = get_term_by('name', $term_value, 'organisation');
                    if (!$term) {
                        // スラッグでも試す
                        $term = get_term_by('slug', $term_value, 'organisation');
                    }
                }
                
                if ($term && !is_wp_error($term)) {
                    // タームのACFフィールドからcolorを取得
                    $color = get_taxonomy_term_color($term);
                    
                    // ターム情報をオブジェクトとして追加
                    $term_objects[] = array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'color' => $color
                    );
                }
            }
            
            // organisationを更新
            if (!empty($term_objects)) {
                $data->data['organisation'] = $term_objects;
            }
        } elseif (is_string($organisation)) {
            // 単一のターム名の場合
            $term = get_term_by('name', $organisation, 'organisation');
            if (!$term) {
                $term = get_term_by('slug', $organisation, 'organisation');
            }
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data['organisation'] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'color' => $color
                );
            }
        } elseif (is_numeric($organisation)) {
            // 単一のタームIDの場合
            $term = get_term($organisation, 'organisation');
            if ($term && !is_wp_error($term)) {
                // タームのACFフィールドからcolorを取得
                $color = get_taxonomy_term_color($term);
                
                // ターム情報をオブジェクトとして追加
                $data->data['organisation'] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'color' => $color
                );
            }
        }
    }

    return $data;
}
// events投稿タイプのREST APIレスポンスに適用（優先度30で、他の処理より後に実行）
add_filter('rest_prepare_events', 'add_color_to_organisation_in_rest_api', 30, 3);

