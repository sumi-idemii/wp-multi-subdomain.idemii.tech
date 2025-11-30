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
 */
function add_acf_fields_to_rest_api($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_fields')) {
        return $data;
    }

    // ACFフィールドを取得
    $acf_fields = get_fields($post->ID);
    
    if ($acf_fields) {
        // ACFフィールドをレスポンスに追加
        $data->data['acf'] = $acf_fields;
    }

    return $data;
}

// すべての投稿タイプに対してACFフィールドを追加
add_filter('rest_prepare_post', 'add_acf_fields_to_rest_api', 10, 3);
add_filter('rest_prepare_page', 'add_acf_fields_to_rest_api', 10, 3);

// カスタム投稿タイプにも適用（例: news）
add_filter('rest_prepare_news', 'add_acf_fields_to_rest_api', 10, 3);

/**
 * タクソノミーフィールドをREST APIに表示する
 * ACFのタクソノミーフィールドの値を取得して表示
 */
function add_taxonomy_field_to_rest_api($data, $post, $request) {
    // ACFプラグインが有効な場合のみ実行
    if (!function_exists('get_field')) {
        return $data;
    }

    // 投稿に紐づくすべてのACFフィールドを取得
    $fields = get_fields($post->ID);
    
    if ($fields) {
        foreach ($fields as $field_name => $field_value) {
            // フィールドの設定を取得
            $field_object = get_field_object($field_name, $post->ID);
            
            // タクソノミーフィールドの場合
            if ($field_object && $field_object['type'] === 'taxonomy') {
                // タクソノミーのターム情報を取得
                $taxonomy = $field_object['taxonomy'];
                $terms = wp_get_post_terms($post->ID, $taxonomy, array('fields' => 'all'));
                
                // ターム情報を整形
                $term_data = array();
                foreach ($terms as $term) {
                    $term_data[] = array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'taxonomy' => $taxonomy,
                    );
                }
                
                // ACFフィールドが存在しない場合は初期化
                if (!isset($data->data['acf'])) {
                    $data->data['acf'] = array();
                }
                
                // タクソノミーフィールドの値を追加
                $data->data['acf'][$field_name] = $term_data;
            }
        }
    }

    return $data;
}

// すべての投稿タイプに対してタクソノミーフィールドを追加
add_filter('rest_prepare_post', 'add_taxonomy_field_to_rest_api', 20, 3);
add_filter('rest_prepare_page', 'add_taxonomy_field_to_rest_api', 20, 3);

// カスタム投稿タイプにも適用（例: news）
add_filter('rest_prepare_news', 'add_taxonomy_field_to_rest_api', 20, 3);

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

