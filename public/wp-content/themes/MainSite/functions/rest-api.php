<?php
/**
 * REST API設定
 * ACFフィールドをREST APIに表示するための設定
 */

/**
 * ACFフィールドをREST APIに追加
 * すべてのACFフィールドをREST APIに表示する
 */
add_filter('acf/rest_api/field_settings/show_in_rest', '__return_true');

/**
 * カスタム投稿タイプのREST APIレスポンスにACFフィールドを追加
 * タクソノミーフィールドの値をターム名に変換
 */
function add_acf_fields_to_rest_api($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_fields')) {
        return $data;
    }

    // ACFフィールドを取得
    $acf_fields = get_fields($post->ID);
    
    if ($acf_fields) {
        // タクソノミーフィールドの値をターム名に変換
        foreach ($acf_fields as $field_name => $field_value) {
            // フィールドの設定を取得
            $field_object = get_field_object($field_name, $post->ID);
            
            // タクソノミーフィールドの場合
            if ($field_object && $field_object['type'] === 'taxonomy') {
                $taxonomy = $field_object['taxonomy'];
                
                // タームIDの配列の場合、ターム名の配列に変換
                if (is_array($field_value) && !empty($field_value)) {
                    $term_names = array();
                    foreach ($field_value as $term_id) {
                        $term = get_term($term_id, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            // ターム名を配列に追加
                            $term_names[] = $term->name;
                        }
                    }
                    $acf_fields[$field_name] = $term_names;
                } elseif (is_numeric($field_value)) {
                    // 単一のタームIDの場合
                    $term = get_term($field_value, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $acf_fields[$field_name] = $term->name;
                    }
                }
            }
        }
        
        // ACFフィールドをレスポンスに追加
        $data->data['acf'] = $acf_fields;
    }

    return $data;
}

/**
 * REST APIレスポンスのルートレベルのタクソノミーフィールドをターム名に変換
 * ACFが自動的に追加したタクソノミーフィールドを変換
 */
function convert_taxonomy_fields_to_names($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field_object')) {
        return $data;
    }

    // データ配列内のすべてのキーをチェック
    if (isset($data->data) && is_array($data->data)) {
        foreach ($data->data as $field_name => $field_value) {
            // フィールド名がタクソノミー名と一致する可能性がある場合
            // ACFのタクソノミーフィールドは通常、タクソノミー名と同じ名前になる
            $taxonomy = $field_name;
            
            // タクソノミーが存在し、値が数値の配列または数値の場合
            if (taxonomy_exists($taxonomy)) {
                // タームIDの配列の場合、ターム名の配列に変換
                if (is_array($field_value) && !empty($field_value) && is_numeric($field_value[0])) {
                    $term_names = array();
                    foreach ($field_value as $term_id) {
                        $term = get_term($term_id, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $term_names[] = $term->name;
                        }
                    }
                    $data->data[$field_name] = $term_names;
                } elseif (is_numeric($field_value)) {
                    // 単一のタームIDの場合
                    $term = get_term($field_value, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $data->data[$field_name] = $term->name;
                    }
                }
            } else {
                // タクソノミーが直接存在しない場合、ACFフィールドとして確認
                $field_object = get_field_object($field_name, $post->ID);
                
                if ($field_object && $field_object['type'] === 'taxonomy') {
                    $taxonomy = $field_object['taxonomy'];
                    
                    // タームIDの配列の場合、ターム名の配列に変換
                    if (is_array($field_value) && !empty($field_value) && is_numeric($field_value[0])) {
                        $term_names = array();
                        foreach ($field_value as $term_id) {
                            $term = get_term($term_id, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                $term_names[] = $term->name;
                            }
                        }
                        $data->data[$field_name] = $term_names;
                    } elseif (is_numeric($field_value)) {
                        // 単一のタームIDの場合
                        $term = get_term($field_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $data->data[$field_name] = $term->name;
                        }
                    }
                }
            }
        }
    }

    return $data;
}

// すべての投稿タイプに対してACFフィールドを追加
add_filter('rest_prepare_post', 'add_acf_fields_to_rest_api', 10, 3);
add_filter('rest_prepare_page', 'add_acf_fields_to_rest_api', 10, 3);

// カスタム投稿タイプにも適用
add_filter('rest_prepare_news', 'add_acf_fields_to_rest_api', 10, 3);
add_filter('rest_prepare_events', 'add_acf_fields_to_rest_api', 10, 3);

// ルートレベルのタクソノミーフィールドを変換（ACFが自動追加したフィールド用）
add_filter('rest_prepare_post', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_page', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_news', 'convert_taxonomy_fields_to_names', 20, 3);
add_filter('rest_prepare_events', 'convert_taxonomy_fields_to_names', 20, 3);


/**
 * タクソノミー自体をREST APIで公開する
 * カスタムタクソノミーがREST APIで公開されていない場合に使用
 */
function register_taxonomy_rest_api_support() {
    // 登録されているすべてのタクソノミーを取得
    $taxonomies = get_taxonomies(array('public' => true), 'objects');
    
    foreach ($taxonomies as $taxonomy) {
        // REST APIで公開されていない場合、公開する
        if (!$taxonomy->show_in_rest) {
            register_taxonomy($taxonomy->name, $taxonomy->object_type, array(
                'show_in_rest' => true,
                'rest_base' => $taxonomy->name,
            ));
        }
    }
}
add_action('init', 'register_taxonomy_rest_api_support', 25);

