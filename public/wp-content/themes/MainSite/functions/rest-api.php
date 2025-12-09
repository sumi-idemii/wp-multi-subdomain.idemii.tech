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
 * events投稿タイプの場合は複数の日程セットを配列として追加
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
        
        // events投稿タイプの場合は複数の日程セットを追加
        if ($post->post_type === 'events' && function_exists('get_event_schedules')) {
            $schedules = get_event_schedules($post->ID);
            $acf_fields['event_schedules'] = $schedules;
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

/**
 * CORSヘッダーを追加してクロスオリジンリクエストを許可
 * ローカルPCのWebサーバーからAPIを取得できるようにする
 */
function add_cors_headers_to_rest_api() {
    // REST APIリクエストでない場合は何もしない
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return;
    }

    // 許可するオリジンのリスト
    // 必要に応じて特定のオリジンのみを許可するように変更可能
    $allowed_origins = array(
        'http://localhost',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:8080',
        // 本番環境のドメイン
        'https://wp-multi-subdomain.idemii.tech',
        'http://wp-multi-subdomain.idemii.tech',
    );

    // リクエスト元のオリジンを取得
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // 開発環境ではすべてのオリジンを許可（必要に応じて変更）
    // 本番環境では特定のオリジンのみを許可することを推奨
    $allow_all_origins = true; // 開発環境用：trueにするとすべてのオリジンを許可

    if ($allow_all_origins) {
        // すべてのオリジンを許可
        header('Access-Control-Allow-Origin: *');
    } else {
        // 許可リストに含まれるオリジンのみ許可
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            // 認証情報を使用する場合は特定のオリジンのみ許可
            header('Access-Control-Allow-Credentials: true');
        }
    }

    // 許可するHTTPメソッド
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

    // 許可するHTTPヘッダー
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');

    // プリフライトリクエスト（OPTIONS）の場合はここで終了
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
}
add_action('rest_api_init', 'add_cors_headers_to_rest_api', 15);

/**
 * REST APIリクエストの前にCORSヘッダーを送信（早期実行）
 * send_headersアクションで確実にヘッダーを送信
 */
function send_cors_headers_early() {
    // REST APIリクエストでない場合は何もしない
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return;
    }

    // 許可するオリジンのリスト
    $allowed_origins = array(
        'http://localhost',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:8080',
        // 本番環境のドメイン
        'https://wp-multi-subdomain.idemii.tech',
        'http://wp-multi-subdomain.idemii.tech',
    );

    // リクエスト元のオリジンを取得
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // 開発環境ではすべてのオリジンを許可
    $allow_all_origins = true;

    if ($allow_all_origins) {
        header('Access-Control-Allow-Origin: *');
    } else {
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        }
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');

    // OPTIONSリクエストの処理
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
}
add_action('send_headers', 'send_cors_headers_early', 1);

